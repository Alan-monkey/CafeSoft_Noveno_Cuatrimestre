from pymongo import MongoClient
from pymongo.server_api import ServerApi
import os
from dotenv import load_dotenv
import certifi
import ssl

load_dotenv()

MONGO_URI = os.getenv("MONGO_URI", "mongodb+srv://al222310501_db_user:xw4ink8eLuCcJSeI@utvt.rkvtgia.mongodb.net/?appName=UTVT")
DATABASE_NAME = "CoffeSoft2"


# Crear cliente con configuración SSL completa
try:
    client = MongoClient(
        MONGO_URI,
        server_api=ServerApi('1'),
        tlsCAFile=certifi.where(),
        tls=True,
        tlsAllowInvalidCertificates=False,
        retryWrites=True,
        w='majority'
    )
    # Probar la conexión
    client.admin.command('ping')
    print("✓ Conexión exitosa a MongoDB Atlas")
except Exception as e:
    print(f"✗ Error de conexión: {e}")
    # Intentar con configuración alternativa
    client = MongoClient(
        MONGO_URI,
        tls=True,
        tlsAllowInvalidCertificates=True
    )
    print("✓ Conexión con certificados deshabilitados")

db = client[DATABASE_NAME]

inventario_collection = db["inventario"]
ventas_collection = db["ventas"]
usuarios_collection = db["usuarios"]
productos_collection = db["tb_productos"]