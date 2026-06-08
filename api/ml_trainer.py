# api/ml_trainer.py
# Ejecutar: source .venv/bin/activate && python ml_trainer.py

from pyspark.sql import SparkSession
from pyspark.sql.functions import col, when, count, sum as spark_sum, round as spark_round
from pyspark.ml.feature import VectorAssembler
from pyspark.ml.classification import DecisionTreeClassifier
from pyspark.ml.evaluation import MulticlassClassificationEvaluator
from pymongo import MongoClient
from pymongo.server_api import ServerApi
from bson import ObjectId
import certifi
import json
import os
from datetime import datetime, timedelta
from dotenv import load_dotenv

load_dotenv()

MONGO_URI = os.getenv("MONGO_URI")
DATABASE_NAME = "CoffeSoft2"


def conectar_mongo():
    client = MongoClient(
        MONGO_URI,
        server_api=ServerApi('1'),
        tlsCAFile=certifi.where(),
        tls=True
    )
    return client, client[DATABASE_NAME]


def leer_ventas_pymongo(db):
    """Lee todas las ventas y aplana el dict de productos."""
    ventas = list(db["ventas"].find())
    filas = []
    for venta in ventas:
        total = float(venta.get("total", 0) or 0)
        productos = venta.get("productos", {})
        if isinstance(productos, dict):
            for _, producto in productos.items():
                nombre   = producto.get("nombre", "")
                cantidad = producto.get("cantidad")
                precio   = producto.get("precio")
                if isinstance(precio, str):
                    precio = precio.replace("$", "").replace(",", "").strip()
                try:
                    cantidad = float(cantidad)
                    precio   = float(precio)
                    if cantidad > 0 and precio > 0:
                        filas.append((nombre, cantidad, precio, total))
                except (TypeError, ValueError):
                    continue
    return filas


def leer_clientes_pymongo(db):
    """Lee ventas del mes actual con usuario_id."""
    inicio_mes = datetime.utcnow().replace(day=1, hour=0, minute=0, second=0, microsecond=0)
    ventas = list(db["ventas"].find(
        {"created_at": {"$gte": inicio_mes}, "usuario_id": {"$exists": True, "$ne": None}},
        {"usuario_id": 1, "total": 1, "created_at": 1}
    ))

    # Mapa usuario_id -> nombre
    usuarios_map = {}
    for venta in ventas:
        uid = str(venta.get("usuario_id", ""))
        if uid and uid not in usuarios_map:
            try:
                usuario = db["usuarios"].find_one({"_id": ObjectId(uid)}, {"nombre": 1})
                usuarios_map[uid] = usuario["nombre"] if usuario else f"Usuario {uid[:6]}"
            except Exception:
                usuarios_map[uid] = f"Usuario {uid[:6]}"

    filas = []
    for v in ventas:
        uid    = str(v.get("usuario_id", ""))
        total  = float(v.get("total", 0) or 0)
        nombre = usuarios_map.get(uid, "Desconocido")
        filas.append((uid, nombre, total))

    return filas


def entrenar(spark, db):
    """Entrena el modelo de árbol de decisión y guarda modelo_meta.json."""
    filas = leer_ventas_pymongo(db)

    if len(filas) < 5:
        print("✗ No hay suficientes datos para entrenar (mínimo 5 registros)")
        return None

    df = spark.createDataFrame(filas, ["nombre", "cantidad", "precio", "total"])
    df = df.withColumn("ingreso", col("cantidad") * col("precio"))

    umbral = df.approxQuantile("ingreso", [0.5], 0.01)[0]

    df = df.withColumn("label",
        when(col("ingreso") >= umbral, 1.0).otherwise(0.0)
    )

    assembler = VectorAssembler(
        inputCols=["cantidad", "precio", "ingreso"],
        outputCol="features",
        handleInvalid="skip"
    )
    df_ml = assembler.transform(df).select("features", "label")
    train, test = df_ml.randomSplit([0.8, 0.2], seed=42)

    dt = DecisionTreeClassifier(featuresCol="features", labelCol="label", maxDepth=5)
    model = dt.fit(train)

    predictions = model.transform(test)
    evaluator = MulticlassClassificationEvaluator(
        labelCol="label", predictionCol="prediction", metricName="accuracy"
    )
    accuracy        = evaluator.evaluate(predictions)
    total_registros = df.count()

    model_path = os.path.join(os.path.dirname(__file__), "modelo_ventas")
    model.write().overwrite().save(model_path)

    meta = {
        "accuracy":         round(accuracy, 4),
        "total_registros":  int(total_registros),
        "umbral":           round(umbral, 2),
        "modelo_path":      model_path,
        "ultima_actualizacion": datetime.utcnow().strftime("%Y-%m-%d %H:%M:%S")
    }
    with open(os.path.join(os.path.dirname(__file__), "modelo_meta.json"), "w") as f:
        json.dump(meta, f)

    print(f"✓ Modelo entrenado | Accuracy: {accuracy:.2%} | Umbral: ${umbral:.2f} | Registros: {total_registros}")
    return meta


def analizar_clientes(spark, db):
    """Analiza clientes frecuentes del mes actual con Spark y guarda clientes_frecuentes.json."""
    filas = leer_clientes_pymongo(db)

    if not filas:
        print("⚠ Sin ventas de clientes este mes")
        with open(os.path.join(os.path.dirname(__file__), "clientes_frecuentes.json"), "w") as f:
            json.dump([], f)
        return []

    df = spark.createDataFrame(filas, ["usuario_id", "nombre", "total"])

    resumen = df.groupBy("usuario_id", "nombre").agg(
        count("*").alias("num_compras"),
        spark_round(spark_sum("total"), 2).alias("total_gastado")
    ).orderBy(col("num_compras").desc())

    resultado = resumen.collect()

    data = [
        {
            "posicion":      i + 1,
            "nombre":        row["nombre"],
            "num_compras":   row["num_compras"],
            "total_gastado": float(row["total_gastado"])
        }
        for i, row in enumerate(resultado)
    ]

    with open(os.path.join(os.path.dirname(__file__), "clientes_frecuentes.json"), "w") as f:
        json.dump(data, f, ensure_ascii=False)

    print(f"✓ Clientes del mes analizados | Total: {len(data)} | Top: {data[0]['nombre'] if data else '—'}")
    return data


def analizar_productos_mes(spark, db):
    """Analiza top 5 productos del mes actual con Spark y guarda productos_mes.json."""
    from datetime import date
    inicio_mes = datetime.utcnow().replace(day=1, hour=0, minute=0, second=0, microsecond=0)

    ventas = list(db["ventas"].find(
        {"created_at": {"$gte": inicio_mes}},
        {"productos": 1}
    ))

    filas = []
    for venta in ventas:
        productos = venta.get("productos", {})
        if isinstance(productos, dict):
            for _, producto in productos.items():
                nombre   = producto.get("nombre", "")
                cantidad = producto.get("cantidad")
                try:
                    cantidad = int(cantidad)
                    if nombre and cantidad > 0:
                        filas.append((nombre, cantidad))
                except (TypeError, ValueError):
                    continue

    if not filas:
        print("⚠ Sin ventas este mes")
        with open(os.path.join(os.path.dirname(__file__), "productos_mes.json"), "w") as f:
            json.dump([], f)
        return []

    df = spark.createDataFrame(filas, ["nombre", "cantidad"])

    resumen = df.groupBy("nombre").agg(
        spark_sum("cantidad").alias("total_vendido")
    ).orderBy(col("total_vendido").desc()).limit(10)

    resultado = resumen.collect()

    data = [{"nombre": row["nombre"], "cantidad": int(row["total_vendido"])} for row in resultado]

    with open(os.path.join(os.path.dirname(__file__), "productos_mes.json"), "w") as f:
        json.dump(data, f, ensure_ascii=False)

    print(f"✓ Productos del mes analizados | Top: {data[0]['nombre'] if data else '—'}")
    return data

def predecir_productos_semana(spark, db):
    """
    Analiza ventas del último mes por producto y predice
    los 3 más y 3 menos vendidos de la próxima semana.
    """
    from pyspark.sql.functions import avg, stddev, col, round as spark_round

    fecha_limite = datetime.utcnow() - timedelta(days=30)
    ventas = list(db["ventas"].find(
        {"created_at": {"$gte": fecha_limite}},
        {"productos": 1, "created_at": 1}
    ))

    filas = []
    for venta in ventas:
        productos = venta.get("productos", {})
        fecha = venta.get("created_at")
        semana = int(fecha.isocalendar()[1]) if fecha else 0
        if isinstance(productos, dict):
            for _, producto in productos.items():
                nombre   = producto.get("nombre", "")
                cantidad = producto.get("cantidad")
                try:
                    cantidad = int(cantidad)
                    if nombre and cantidad > 0:
                        filas.append((nombre, cantidad, semana))
                except (TypeError, ValueError):
                    continue

    if not filas:
        print("⚠ Sin datos para predicción de productos")
        with open(os.path.join(os.path.dirname(__file__), "prediccion_productos.json"), "w") as f:
            json.dump({"mas_vendidos": [], "menos_vendidos": []}, f)
        return

    df = spark.createDataFrame(filas, ["nombre", "cantidad", "semana"])

    # Promedio y desviación por producto por semana
    resumen = df.groupBy("nombre").agg(
        avg("cantidad").alias("promedio_semanal"),
        stddev("cantidad").alias("desviacion"),
        spark_sum("cantidad").alias("total_mes")
    )

    # Predicción = promedio + tendencia simple
    resumen = resumen.withColumn(
        "prediccion_semana",
        spark_round(col("promedio_semanal") * 1.05, 1)  # +5% tendencia
    ).orderBy(col("prediccion_semana").desc())

    todos = resumen.collect()

    mas_vendidos   = [
        {
            "nombre":           r["nombre"],
            "prediccion":       float(r["prediccion_semana"]),
            "promedio_semanal": round(float(r["promedio_semanal"]), 1),
            "total_mes":        int(r["total_mes"])
        }
        for r in todos[:5]
    ]
    menos_vendidos = [
        {
            "nombre":           r["nombre"],
            "prediccion":       float(r["prediccion_semana"]),
            "promedio_semanal": round(float(r["promedio_semanal"]), 1),
            "total_mes":        int(r["total_mes"])
        }
        for r in todos[-5:]
    ]
    menos_vendidos.reverse()

    data = {"mas_vendidos": mas_vendidos, "menos_vendidos": menos_vendidos}

    with open(os.path.join(os.path.dirname(__file__), "prediccion_productos.json"), "w") as f:
        json.dump(data, f, ensure_ascii=False)

    print(f"✓ Predicción productos | Top: {mas_vendidos[0]['nombre'] if mas_vendidos else '—'}")
    return data


#REGRESION LINAL PARA INSUMOS


def predecir_insumos_semana(spark, db):
    """
    Regresión lineal para predecir cuánto insumo se necesitará
    la próxima semana basándose en el historial de ventas.
    Guarda prediccion_insumos.json
    """
    from pyspark.ml.regression import LinearRegression
    from pyspark.ml.feature import VectorAssembler
    from pyspark.sql.functions import col, sum as spark_sum, avg, round as spark_round

    fecha_limite = datetime.utcnow() - timedelta(days=30)

    # Leer ventas recientes
    ventas = list(db["ventas"].find(
        {"created_at": {"$gte": fecha_limite}},
        {"productos": 1, "created_at": 1}
    ))

    if not ventas:
        print("⚠ Sin ventas para predecir insumos")
        with open(os.path.join(os.path.dirname(__file__), "prediccion_insumos.json"), "w") as f:
            json.dump([], f)
        return []

    # Acumular cantidad vendida por producto_id
    ventas_por_producto = {}
    for venta in ventas:
        productos = venta.get("productos", {})
        if isinstance(productos, dict):
            items = productos.values()
        elif isinstance(productos, list):
            items = productos
        else:
            continue

        for producto in items:
            pid = str(producto.get("producto_id", ""))
            cantidad = producto.get("cantidad", 0)
            try:
                cantidad = float(cantidad)
                if pid and cantidad > 0:
                    ventas_por_producto[pid] = ventas_por_producto.get(pid, 0) + cantidad
            except (TypeError, ValueError):
                continue

    if not ventas_por_producto:
        print("⚠ No se pudo acumular ventas por producto")
        with open(os.path.join(os.path.dirname(__file__), "prediccion_insumos.json"), "w") as f:
            json.dump([], f)
        return []

    # Para cada producto, obtener sus insumos y cantidades
    filas_insumos = {}  # insumo_id -> {nombre, tipo, cantidad_acumulada, unidad}

    for pid, total_vendido in ventas_por_producto.items():
        try:
            from bson import ObjectId
            producto = db["tb_productos"].find_one({"_id": ObjectId(pid)})
        except Exception:
            producto = db["tb_productos"].find_one({"_id": pid})

        if not producto:
            continue

        insumos          = producto.get("insumos", [])
        insumos_cantidad = producto.get("insumos_cantidad", {})

        for insumo_id in insumos:
            cantidad_por_unidad = float(insumos_cantidad.get(str(insumo_id), 0) or 0)
            if cantidad_por_unidad <= 0:
                continue

            consumo_total = cantidad_por_unidad * total_vendido

            if insumo_id not in filas_insumos:
                # Buscar nombre y tipo del insumo
                try:
                    insumo_doc = db["Insumos"].find_one({"_id": ObjectId(insumo_id)})
                except Exception:
                    insumo_doc = db["Insumos"].find_one({"_id": insumo_id})

                nombre_insumo = insumo_doc["nombre"] if insumo_doc else str(insumo_id)
                tipo_insumo   = insumo_doc.get("tipo", "unidad") if insumo_doc else "unidad"
                stock_actual  = float(insumo_doc.get("cantidad", 0) or 0) if insumo_doc else 0

                filas_insumos[insumo_id] = {
                    "nombre":         nombre_insumo,
                    "tipo":           tipo_insumo,
                    "consumo_mes":    0.0,
                    "stock_actual":   stock_actual,
                }

            filas_insumos[insumo_id]["consumo_mes"] += consumo_total

    if not filas_insumos:
        print("⚠ No se encontraron insumos asociados a las ventas")
        with open(os.path.join(os.path.dirname(__file__), "prediccion_insumos.json"), "w") as f:
            json.dump([], f)
        return []

    # Construir DataFrame Spark para regresión
    # Features: consumo_mes (últimos 30 días)
    # Label: necesidad_semana = consumo_mes / 4 (promedio semanal)
    filas_spark = []
    for iid, data in filas_insumos.items():
        consumo_mes     = data["consumo_mes"]
        necesidad_sem   = consumo_mes / 4.0
        filas_spark.append((
            str(iid),
            data["nombre"],
            data["tipo"],
            float(consumo_mes),
            float(necesidad_sem),
            float(data["stock_actual"]),
        ))

    df = spark.createDataFrame(
        filas_spark,
        ["insumo_id", "nombre", "tipo", "consumo_mes", "necesidad_semana", "stock_actual"]
    )

    # Solo entrenar regresión si hay suficientes filas
    resultado = []
    if df.count() >= 3:
        assembler = VectorAssembler(
            inputCols=["consumo_mes"],
            outputCol="features",
            handleInvalid="skip"
        )
        df_ml = assembler.transform(df)
        train, test = df_ml.randomSplit([0.8, 0.2], seed=42)

        lr = LinearRegression(
            featuresCol="features",
            labelCol="necesidad_semana",
            regParam=0.1
        )

        # Si train tiene al menos 1 fila
        if train.count() > 0:
            model = lr.fit(train)
            predicciones = model.transform(df_ml)

            rows = predicciones.select(
                "insumo_id", "nombre", "tipo",
                "consumo_mes", "necesidad_semana",
                "prediction", "stock_actual"
            ).collect()

            for row in rows:
                pred_semana  = max(0.0, float(row["prediction"]))
                stock        = float(row["stock_actual"])
                dias_restantes = round((stock / pred_semana * 7), 1) if pred_semana > 0 else None

                resultado.append({
                    "nombre":              row["nombre"],
                    "tipo":                row["tipo"],
                    "consumo_mes":         round(float(row["consumo_mes"]), 3),
                    "necesidad_semana":    round(pred_semana, 3),
                    "stock_actual":        round(stock, 3),
                    "dias_restantes":      dias_restantes,
                    "alerta":             stock < pred_semana
                })
        else:
            # Fallback sin regresión
            for iid, data in filas_insumos.items():
                pred_semana = data["consumo_mes"] / 4.0
                stock       = data["stock_actual"]
                dias_restantes = round((stock / pred_semana * 7), 1) if pred_semana > 0 else None
                resultado.append({
                    "nombre":           data["nombre"],
                    "tipo":             data["tipo"],
                    "consumo_mes":      round(data["consumo_mes"], 3),
                    "necesidad_semana": round(pred_semana, 3),
                    "stock_actual":     round(stock, 3),
                    "dias_restantes":   dias_restantes,
                    "alerta":          stock < pred_semana
                })
    else:
        for iid, data in filas_insumos.items():
            pred_semana = data["consumo_mes"] / 4.0
            stock       = data["stock_actual"]
            dias_restantes = round((stock / pred_semana * 7), 1) if pred_semana > 0 else None
            resultado.append({
                "nombre":           data["nombre"],
                "tipo":             data["tipo"],
                "consumo_mes":      round(data["consumo_mes"], 3),
                "necesidad_semana": round(pred_semana, 3),
                "stock_actual":     round(stock, 3),
                "dias_restantes":   dias_restantes,
                "alerta":          stock < pred_semana
            })

    resultado.sort(key=lambda x: x["necesidad_semana"], reverse=True)

    with open(os.path.join(os.path.dirname(__file__), "prediccion_insumos.json"), "w") as f:
        json.dump(resultado, f, ensure_ascii=False)

    alertas = sum(1 for r in resultado if r["alerta"])
    print(f"✓ Predicción insumos | Total: {len(resultado)} | Alertas stock bajo: {alertas}")
    return resultado

#ARBOL DE DESICIONES

def clasificar_insumos_arbol(spark, db):
    """
    Árbol de decisión enfocado en insumos próximos a caducar (≤14 días).
    Propone estrategias de venta: descuento individual, paquete o liquidación urgente.
    Guarda clasificacion_insumos.json
    """
    from pyspark.ml.classification import DecisionTreeClassifier
    from pyspark.ml.feature import VectorAssembler
    from datetime import date

    insumos = list(db["Insumos"].find())
    if not insumos:
        print("⚠ Sin insumos para clasificar")
        with open(os.path.join(os.path.dirname(__file__), "clasificacion_insumos.json"), "w") as f:
            json.dump([], f)
        return []

    # Cargar necesidad semanal del JSON de regresión si existe
    pred_path = os.path.join(os.path.dirname(__file__), "prediccion_insumos.json")
    necesidad_map = {}
    if os.path.exists(pred_path):
        with open(pred_path, "r", encoding="utf-8") as f:
            preds = json.load(f)
        for p in preds:
            necesidad_map[p["nombre"]] = p.get("necesidad_semana", 0)

    # Buscar productos que usan cada insumo
    productos_por_insumo = {}
    productos = list(db["tb_productos"].find({}, {"nombre": 1, "precio": 1, "insumos": 1}))
    for prod in productos:
        for iid in prod.get("insumos", []):
            key = str(iid)
            if key not in productos_por_insumo:
                productos_por_insumo[key] = []
            productos_por_insumo[key].append({
                "nombre": prod.get("nombre", ""),
                "precio": float(prod.get("precio", 0)),
            })

    hoy = datetime.utcnow().date()
    filas = []
    meta  = []  # datos completos para el JSON final

    for ins in insumos:
        nombre    = ins.get("nombre", "")
        cantidad  = float(ins.get("cantidad", 0) or 0)
        caducidad = ins.get("caducidad", "NA")
        iid       = str(ins.get("_id", ""))

        # Calcular días para caducar
        if caducidad and caducidad != "NA":
            try:
                fecha_cad = datetime.strptime(caducidad[:10], "%Y-%m-%d").date()
                dias_cad  = (fecha_cad - hoy).days
            except Exception:
                dias_cad = 999
        else:
            dias_cad = 999  # no caduca

        necesidad_sem = float(necesidad_map.get(nombre, 0))
        ratio_consumo = (cantidad / necesidad_sem) if necesidad_sem > 0 else 999.0

        # Etiqueta de entrenamiento basada en reglas de negocio
        if dias_cad > 14:
            label = 0.0  # Sin urgencia
        elif dias_cad <= 3:
            label = 3.0  # Liquidar urgente
        elif dias_cad <= 7:
            label = 2.0  # Descuento por paquete
        else:
            label = 1.0  # Descuento individual (8-14 días)

        filas.append((
            nombre,
            float(dias_cad) if dias_cad < 999 else 999.0,
            cantidad,
            necesidad_sem,
            ratio_consumo,
            label,
            iid,
            caducidad,
        ))

    if len(filas) < 3:
        print("⚠ Pocos insumos para entrenar árbol")
        with open(os.path.join(os.path.dirname(__file__), "clasificacion_insumos.json"), "w") as f:
            json.dump([], f)
        return []

    df = spark.createDataFrame(
        [(f[0], f[1], f[2], f[3], f[4], f[5]) for f in filas],
        ["nombre", "dias_cad", "cantidad", "necesidad_sem", "ratio_consumo", "label"]
    )

    assembler = VectorAssembler(
        inputCols=["dias_cad", "cantidad", "necesidad_sem", "ratio_consumo"],
        outputCol="features",
        handleInvalid="skip"
    )
    df_ml = assembler.transform(df)
    train, test = df_ml.randomSplit([0.8, 0.2], seed=42)
    if train.count() == 0:
        train = df_ml

    dt = DecisionTreeClassifier(featuresCol="features", labelCol="label", maxDepth=4)
    model = dt.fit(train)
    predicciones = model.transform(df_ml)

    etiquetas = {
        0.0: "Sin urgencia",
        1.0: "Descuento individual",
        2.0: "Descuento por paquete",
        3.0: "Liquidar urgente",
    }
    descuentos = {
        0.0: 0,
        1.0: 10,   # 10% descuento individual
        2.0: 20,   # 20% en paquete
        3.0: 35,   # 35% liquidación
    }
    niveles = {
        0.0: "success",
        1.0: "info",
        2.0: "warning",
        3.0: "danger",
    }

    rows = predicciones.select(
        "nombre", "dias_cad", "cantidad", "necesidad_sem", "ratio_consumo", "prediction"
    ).collect()

    # Construir resultado con propuestas
    resultado = []
    fila_map  = {f[0]: f for f in filas}

    for row in rows:
        pred     = float(row["prediction"])
        nombre   = row["nombre"]
        dias_cad = float(row["dias_cad"])
        iid      = fila_map[nombre][6] if nombre in fila_map else ""
        caducidad_raw = fila_map[nombre][7] if nombre in fila_map else "NA"

        prods_asociados = productos_por_insumo.get(iid, [])
        descuento_pct   = descuentos[pred]

        propuestas = []
        for prod in prods_asociados:
            precio_orig     = prod["precio"]
            precio_desc     = round(precio_orig * (1 - descuento_pct / 100), 2)
            if pred == 2.0:
                # Propuesta de paquete: 2x1 o 3 por el precio de 2
                propuestas.append({
                    "producto":      prod["nombre"],
                    "precio_normal": precio_orig,
                    "propuesta":     f"Paquete 2x1 — ${precio_orig} los dos",
                    "ahorro":        round(precio_orig, 2),
                })
            elif pred >= 1.0:
                propuestas.append({
                    "producto":      prod["nombre"],
                    "precio_normal": precio_orig,
                    "propuesta":     f"{descuento_pct}% descuento — ${precio_desc}",
                    "ahorro":        round(precio_orig - precio_desc, 2),
                })

        resultado.append({
            "nombre":       nombre,
            "caducidad":    caducidad_raw,
            "dias_restantes": int(dias_cad) if dias_cad < 999 else None,
            "cantidad":     round(float(row["cantidad"]), 3),
            "necesidad_sem": round(float(row["necesidad_sem"]), 3),
            "clasificacion": etiquetas[pred],
            "nivel":         niveles[pred],
            "descuento_pct": descuento_pct,
            "propuestas":    propuestas,
            "prediccion":    pred,
        })

    # Solo mostrar los que caducan en ≤14 días, ordenados por urgencia
    resultado_filtrado = [r for r in resultado if r["dias_restantes"] is not None and r["dias_restantes"] <= 14]
    resultado_filtrado.sort(key=lambda x: x["dias_restantes"])

    with open(os.path.join(os.path.dirname(__file__), "clasificacion_insumos.json"), "w") as f:
        json.dump(resultado_filtrado, f, ensure_ascii=False)

    print(f"✓ Árbol caducidad | Próx. a caducar (≤14d): {len(resultado_filtrado)} insumos")
    return resultado_filtrado

if __name__ == "__main__":
    client, db = conectar_mongo()

    spark = SparkSession.builder \
        .appName("CoffeSoftML") \
        .getOrCreate()
    spark.sparkContext.setLogLevel("ERROR")

    entrenar(spark, db)
    analizar_clientes(spark, db)
    analizar_productos_mes(spark, db)
    predecir_productos_semana(spark, db) 
    predecir_insumos_semana(spark, db)
    clasificar_insumos_arbol(spark, db)


    spark.stop()
    client.close()

