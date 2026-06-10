# api/kmeans.py

import os
import json
from itertools import combinations
from collections import defaultdict
from dotenv import load_dotenv
from pymongo import MongoClient
from pymongo.server_api import ServerApi
from bson import ObjectId
import certifi

load_dotenv()

MONGO_URI = os.getenv(
    "MONGO_URI",
    "mongodb+srv://al222310501_db_user:xw4ink8eLuCcJSeI@utvt.rkvtgia.mongodb.net/?appName=UTVT"
)

DATABASE_NAME = "CoffeSoft2"

client = MongoClient(
    MONGO_URI,
    server_api=ServerApi("1"),
    tlsCAFile=certifi.where(),
    tls=True
)

db = client[DATABASE_NAME]

ventas_collection = db["ventas"]
productos_collection = db["tb_productos"]


def limpiar_numero(valor):
    if valor is None:
        return 0.0

    if isinstance(valor, str):
        valor = valor.replace("$", "").replace(",", "").strip()

    try:
        return float(valor)
    except (TypeError, ValueError):
        return 0.0


def limpiar_nombre(valor):
    if valor is None:
        return ""
    return str(valor).strip()


def es_object_id(valor):
    try:
        ObjectId(str(valor))
        return True
    except Exception:
        return False


def obtener_items_productos(productos):
    if isinstance(productos, dict):
        return productos.values()

    if isinstance(productos, list):
        return productos

    return []


def buscar_producto_por_id(producto_id):
    if not producto_id:
        return None

    try:
        producto = productos_collection.find_one({"_id": producto_id})
        if producto:
            return producto
    except Exception:
        pass

    try:
        producto = productos_collection.find_one({"_id": ObjectId(str(producto_id))})
        if producto:
            return producto
    except Exception:
        pass

    return None


def buscar_producto_por_nombre(nombre):
    nombre = limpiar_nombre(nombre)

    if not nombre:
        return None

    return (
        productos_collection.find_one({"nombre": nombre})
        or productos_collection.find_one({"name": nombre})
        or productos_collection.find_one({"producto": nombre})
        or productos_collection.find_one({"descripcion": nombre})
    )


def obtener_nombre_desde_documento(producto_db):
    if not producto_db:
        return ""

    return limpiar_nombre(
        producto_db.get("nombre")
        or producto_db.get("name")
        or producto_db.get("producto")
        or producto_db.get("descripcion")
        or ""
    )


def obtener_precio_desde_documento(producto_db):
    if not producto_db:
        return 0.0

    return limpiar_numero(
        producto_db.get("precio")
        or producto_db.get("price")
        or producto_db.get("precio_unitario")
        or 0
    )


def resolver_producto(producto_venta):
    """
    Devuelve un producto normalizado:
    {
        "nombre": "...",
        "precio": 0.0
    }

    Soporta ventas donde productos viene así:
    {
        "producto_id": ObjectId("...")
    }

    o así:
    {
        "nombre": "...",
        "precio": 35
    }
    """

    if not isinstance(producto_venta, dict):
        return None

    nombre_directo = limpiar_nombre(
        producto_venta.get("nombre")
        or producto_venta.get("name")
        or producto_venta.get("producto")
        or producto_venta.get("descripcion")
        or ""
    )

    precio_directo = limpiar_numero(
        producto_venta.get("precio")
        or producto_venta.get("price")
        or producto_venta.get("precio_unitario")
        or producto_venta.get("subtotal")
        or 0
    )

    producto_db = None

    producto_id = (
        producto_venta.get("producto_id")
        or producto_venta.get("_id")
        or producto_venta.get("id")
    )

    if producto_id:
        producto_db = buscar_producto_por_id(producto_id)

    if not producto_db and nombre_directo:
        producto_db = buscar_producto_por_nombre(nombre_directo)

    nombre_final = nombre_directo or obtener_nombre_desde_documento(producto_db)
    precio_final = precio_directo or obtener_precio_desde_documento(producto_db)

    if not nombre_final:
        return None

    return {
        "nombre": nombre_final,
        "precio": precio_final
    }


def calcular_descuento(frecuencia):
    if frecuencia >= 10:
        return 0.15
    if frecuencia >= 5:
        return 0.10
    if frecuencia >= 3:
        return 0.05
    return 0.0


def clasificar_combo(frecuencia):
    if frecuencia >= 10:
        return "Combo fuerte"
    if frecuencia >= 5:
        return "Combo recomendado"
    if frecuencia >= 3:
        return "Combo potencial"
    return "Combo exploratorio"


def generar_accion(tipo, combo):
    if tipo == "Combo fuerte":
        return f"Crear promoción principal con {combo}"

    if tipo == "Combo recomendado":
        return f"Ofrecer descuento moderado para {combo}"

    if tipo == "Combo potencial":
        return f"Probar combo temporal con {combo}"

    return f"Monitorear comportamiento de {combo}"


def ejecutar_kmeans_productos():
    conteo_combos = defaultdict(int)
    precios_detectados = defaultdict(list)

    ventas_analizadas = 0
    ventas_con_dos_o_mas_productos = 0

    ventas = list(ventas_collection.find())

    for venta in ventas:
        ventas_analizadas += 1
        productos = venta.get("productos", {})

        productos_venta = {}

        for producto_raw in obtener_items_productos(productos):
            producto = resolver_producto(producto_raw)

            if not producto:
                continue

            nombre = producto["nombre"]
            precio = producto["precio"]

            productos_venta[nombre] = precio

            if precio > 0:
                precios_detectados[nombre].append(precio)

        nombres = sorted(list(productos_venta.keys()))

        if len(nombres) < 2:
            continue

        ventas_con_dos_o_mas_productos += 1

        for producto_a, producto_b in combinations(nombres, 2):
            clave = f"{producto_a} + {producto_b}"
            conteo_combos[clave] += 1

    resultado = []

    for combo, frecuencia in conteo_combos.items():
        producto_1, producto_2 = combo.split(" + ")

        if precios_detectados[producto_1]:
            precio_1 = sum(precios_detectados[producto_1]) / len(precios_detectados[producto_1])
        else:
            producto_db_1 = buscar_producto_por_nombre(producto_1)
            precio_1 = obtener_precio_desde_documento(producto_db_1)

        if precios_detectados[producto_2]:
            precio_2 = sum(precios_detectados[producto_2]) / len(precios_detectados[producto_2])
        else:
            producto_db_2 = buscar_producto_por_nombre(producto_2)
            precio_2 = obtener_precio_desde_documento(producto_db_2)

        precio_normal = precio_1 + precio_2
        descuento = calcular_descuento(frecuencia)
        precio_sugerido = precio_normal * (1 - descuento)
        ahorro = precio_normal - precio_sugerido

        porcentaje_aparicion = (
            (frecuencia / ventas_con_dos_o_mas_productos) * 100
            if ventas_con_dos_o_mas_productos > 0
            else 0
        )

        tipo = clasificar_combo(frecuencia)

        resultado.append({
            "combo": combo,
            "producto_1": producto_1,
            "producto_2": producto_2,
            "frecuencia": int(frecuencia),
            "ventas_analizadas": int(ventas_analizadas),
            "ventas_con_dos_o_mas_productos": int(ventas_con_dos_o_mas_productos),
            "porcentaje_aparicion": round(porcentaje_aparicion, 2),
            "precio_producto_1": round(float(precio_1), 2),
            "precio_producto_2": round(float(precio_2), 2),
            "precio_normal": round(float(precio_normal), 2),
            "descuento_sugerido": int(descuento * 100),
            "precio_combo_sugerido": round(float(precio_sugerido), 2),
            "ahorro_cliente": round(float(ahorro), 2),
            "recomendacion_tipo": tipo,
            "recomendacion": generar_accion(tipo, combo)
        })

    resultado = sorted(
        resultado,
        key=lambda x: x["frecuencia"],
        reverse=True
    )[:10]

    for index, item in enumerate(resultado, start=1):
        item["posicion"] = index

    output_path = os.path.join(
        os.path.dirname(__file__),
        "kmeans_productos.json"
    )

    with open(output_path, "w", encoding="utf-8") as archivo:
        json.dump(resultado, archivo, ensure_ascii=False, indent=4)

    print("✓ Análisis de combos generado correctamente")
    print(f"Archivo generado: {output_path}")
    print(f"Ventas analizadas: {ventas_analizadas}")
    print(f"Ventas con 2 o más productos: {ventas_con_dos_o_mas_productos}")
    print(f"Combos detectados: {len(resultado)}")

    return resultado 


if __name__ == "__main__":
    try:
        ejecutar_kmeans_productos()
    finally:
        client.close()