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

    spark.stop()
    client.close()

