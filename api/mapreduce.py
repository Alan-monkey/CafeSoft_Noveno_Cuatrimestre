# api/mapreduce.py

import os
import json
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
insumos_collection = db["Insumos"]


def limpiar_numero(valor):
    if valor is None:
        return 0.0

    if isinstance(valor, str):
        valor = valor.replace("$", "").replace(",", "").strip()

    try:
        return float(valor)
    except (TypeError, ValueError):
        return 0.0


def normalizar_texto(valor):
    if valor is None:
        return ""

    return str(valor).strip()


def es_object_id(valor):
    valor = normalizar_texto(valor)

    if len(valor) != 24:
        return False

    try:
        ObjectId(valor)
        return True
    except Exception:
        return False


def obtener_items_productos(productos):
    if isinstance(productos, dict):
        return productos.values()

    if isinstance(productos, list):
        return productos

    return []


def buscar_nombre_insumo(valor):
    texto = normalizar_texto(valor)

    if not texto:
        return "Insumo sin nombre"

    if not es_object_id(texto):
        return texto

    try:
        insumo = insumos_collection.find_one({
            "_id": ObjectId(texto)
        })

        if insumo:
            return (
                insumo.get("nombre")
                or insumo.get("insumo")
                or insumo.get("descripcion")
                or f"Insumo {texto}"
            )

    except Exception:
        pass

    return f"Insumo {texto}"


def buscar_producto_en_catalogo(nombre_producto):
    nombre_producto = normalizar_texto(nombre_producto)

    if not nombre_producto:
        return None

    return (
        productos_collection.find_one({"nombre": nombre_producto})
        or productos_collection.find_one({"name": nombre_producto})
        or productos_collection.find_one({"producto": nombre_producto})
        or productos_collection.find_one({"descripcion": nombre_producto})
    )


def obtener_nombre_producto_vendido(producto_vendido):
    nombre = (
        producto_vendido.get("nombre")
        or producto_vendido.get("name")
        or producto_vendido.get("producto")
        or producto_vendido.get("descripcion")
        or ""
    )

    return normalizar_texto(nombre)


def obtener_insumos_producto(producto_db):
    if not producto_db:
        return []

    insumos = producto_db.get("insumos") or []
    cantidades = producto_db.get("insumos_cantidad") or {}

    if not isinstance(insumos, list):
        return []

    resultado = []
 
    for insumo in insumos:
        if isinstance(insumo, dict):
            raw_id = (
                insumo.get("_id")
                or insumo.get("id")
                or insumo.get("insumo_id")
                or insumo.get("inventario_id")
                or insumo.get("producto_id")
                or insumo.get("nombre")
                or insumo.get("name")
                or insumo.get("insumo")
            )

            nombre_insumo = buscar_nombre_insumo(raw_id)

            cantidad = limpiar_numero(
                insumo.get("cantidad")
                or insumo.get("cantidad_por_producto")
                or insumo.get("cantidad_usada")
                or cantidades.get(str(raw_id))
                or 1
            )

        elif isinstance(insumo, str):
            nombre_insumo = buscar_nombre_insumo(insumo)

            cantidad = limpiar_numero(
                cantidades.get(insumo, 1)
            )

        else:
            continue

        if cantidad <= 0:
            cantidad = 1

        resultado.append({
            "insumo": nombre_insumo,
            "cantidad": cantidad
        })

    return resultado


def ejecutar_mapreduce_insumos():
    consumo_por_insumo = defaultdict(lambda: defaultdict(lambda: {
        "producto": "",
        "cantidad_vendida": 0.0,
        "consumo_estimado": 0.0
    }))

    ventas = list(ventas_collection.find())

    for venta in ventas:
        productos = venta.get("productos", {})

        for producto_vendido in obtener_items_productos(productos):
            if not isinstance(producto_vendido, dict):
                continue

            nombre_producto = obtener_nombre_producto_vendido(producto_vendido)

            cantidad_vendida = limpiar_numero(
                producto_vendido.get("cantidad")
                or producto_vendido.get("qty")
                or producto_vendido.get("quantity")
                or 0
            )

            if not nombre_producto or cantidad_vendida <= 0:
                continue

            producto_db = buscar_producto_en_catalogo(nombre_producto)

            if not producto_db:
                continue

            insumos = obtener_insumos_producto(producto_db)

            if not insumos:
                continue

            for insumo_data in insumos:
                nombre_insumo = insumo_data["insumo"]
                cantidad_por_producto = limpiar_numero(insumo_data["cantidad"])

                if not nombre_insumo:
                    nombre_insumo = "Insumo sin nombre"

                if cantidad_por_producto <= 0:
                    cantidad_por_producto = 1

                consumo_estimado = cantidad_por_producto * cantidad_vendida

                ref = consumo_por_insumo[nombre_insumo][nombre_producto]
                ref["producto"] = nombre_producto
                ref["cantidad_vendida"] += cantidad_vendida
                ref["consumo_estimado"] += consumo_estimado

    resultado = []

    for nombre_insumo, productos_dict in consumo_por_insumo.items():
        productos = list(productos_dict.values())

        productos_ordenados = sorted(
            productos,
            key=lambda x: x["consumo_estimado"],
            reverse=True
        )

        ranking = []

        for index, producto in enumerate(productos_ordenados[:10], start=1):
            ranking.append({
                "posicion": index,
                "producto": producto["producto"],
                "cantidad_vendida": round(float(producto["cantidad_vendida"]), 2),
                "consumo_estimado": round(float(producto["consumo_estimado"]), 2)
            })

        resultado.append({
            "insumo": nombre_insumo,
            "total_consumido": round(
                sum(p["consumo_estimado"] for p in productos),
                2
            ),
            "ranking_productos": ranking
        })

    resultado = sorted(
        resultado,
        key=lambda x: x["total_consumido"],
        reverse=True
    )

    guardar_json(resultado)

    print("✓ MapReduce comparativo generado correctamente")
    print(f"Insumos analizados: {len(resultado)}")

    return resultado


def guardar_json(data):
    output_path = os.path.join(
        os.path.dirname(__file__),
        "mapreduce_insumos.json"
    )

    with open(output_path, "w", encoding="utf-8") as archivo:
        json.dump(data, archivo, ensure_ascii=False, indent=4)

    print(f"Archivo generado: {output_path}")


if __name__ == "__main__":
    try:
        ejecutar_mapreduce_insumos()
    finally:
        client.close()