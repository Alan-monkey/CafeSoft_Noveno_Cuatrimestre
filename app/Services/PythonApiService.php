<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonApiService
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = config('python_api.base_url');
        $this->timeout = config('python_api.timeout');
    }

    /**
     * Obtener todos los productos
     */
    public function getProductos()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . '/productos');

            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData['data'] ?? [] // Extraer el array de productos
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al obtener productos',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::getProductos: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener un producto por ID
     */
    public function getProducto($id)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . "/productos/{$id}");

            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData['data'] ?? null // Extraer el producto
                ];
            }

            return [
                'success' => false,
                'error' => 'Producto no encontrado',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::getProducto: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear un nuevo producto
     */
    public function createProducto($data)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/productos', $data);

            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData['data'] ?? $jsonData // Extraer el producto creado
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al crear producto',
                'status' => $response->status(),
                'message' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::createProducto: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar un producto
     */
    public function updateProducto($id, $data)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->put($this->baseUrl . "/productos/{$id}", $data);

            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData['data'] ?? $jsonData // Extraer el producto actualizado
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al actualizar producto',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::updateProducto: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar un producto
     */
    public function deleteProducto($id)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->delete($this->baseUrl . "/productos/{$id}");

            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData // La respuesta de delete ya tiene success y message
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al eliminar producto',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::deleteProducto: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    public function getInventario()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/inventario');  // ← CORREGIR: baseUrl (con "e")

        if ($response->successful()) {  // ← CORREGIR: successful (con "ss")
            $jsonData = $response->json();
            return [
                'success' => true,
                'data' => $jsonData['data'] ?? []
            ];
        }

        return [
            'success' => false,
            'error' => 'Error al obtener inventario',
            'status' => $response->status()
        ];
    } catch (\Exception $e) {
        Log::error('Error en PythonApiService::getInventario: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

    public function getInventarioByProducto($productoId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->basUrl . "/inventario/producto/{$productoId}");
            
                if ($response->successful()) {
                    $jsondata = $response->json();
                    return [
                        'success' => true,
                        'data' => $jsondata['data'] ?? null
                    ];
                }

                return [
                    'success' => false,
                    'erro' => 'Inventario no encontrado',
                    'status' => $response->status()
                ];
        } catch (\Exception $e) {
            Log::error('Error en PythonService::getInventarioByProducto: ' . $e->getMessage());
            return [
                'success' => flase,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateInventarioByProducto($productoId, $data)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->put($this->baseUrl . "/inventario/producto/{$productoId}", $data);

            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData['data'] ?? $jsonData
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al actualizar inventario',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::updateInventarioByProducto: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas del inventario
     */
    public function getInventarioEstadisticas()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . '/inventario/resumen/estadisticas');

            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData['data'] ?? []
                ];
            }

            return [
                'success' => false,
                'error' => 'Error al obtener estadísticas',
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::getInventarioEstadisticas: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getProductoById($id)
    {
        return $this->getProducto($id);
    }

    public function verificarStock($productoId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . "/inventario/producto/{$productoId}");
            if ($response->successful()) {
                $jsonData = $response->json();
                return [
                    'success' => true,
                    'data' => $jsonData['data'] ?? null
                ];
            }
            return [
                'success' => false,
                'error' => "No se pudo obtener el stock",
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Error en PythonApiService::verificarStock: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

    
    }

    public function actualizarStockMasivo($items)
    {
        $resultados = [];
        $errores = [];

        foreach ($items as $item) {
            $productoId = $item['id'];
            $cantidadVendida = $item['cantidad'];

            $stockResponse = $this->verificarStock($productoId);

            if (!$stockResponse['success']) {
                $errores[] = "Error al verificar stock de producto {$productoId}";
                continue;
            }

            $inventario = $stockResponse['data'];
            $nuevoStock = $inventario['stock_actual'] - $cantidadVendida;

            $updateData = [
                'stock_actual' => $nuevoStock
            ];

            $updateResponse = $this->updateInventarioByProducto($productoId, $updateData);

            if ($updateResponse['success']) {
                $resultados[] = [
                    'producto_id' => $productoId,
                    'stock_anterior' => $inventario['stock_actual'],
                    'stock_nuevo' => $nuevoStock,
                    'success' => true
                ];
            } else {
                $errores[] = "Error al actualizar el stock de producto {$productoId}";
            }
        }
        
        return [
            'success' => empty($errores),
            'resultados' => $resultados,
            'errores' => $errores,
        ];
    }


    // ============= MÉTODOS DE USUARIOS =============

public function getUsuarios()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/usuarios');
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }
        return ['success' => false, 'error' => 'Error al obtener usuarios'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getUsuarios: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function getUsuario($id)
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . "/usuarios/{$id}");
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? null];
        }
        return ['success' => false, 'error' => 'Usuario no encontrado'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getUsuario: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function getUsuarioByEmail($email)
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/usuarios/email/' . urlencode($email));
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? null];
        }
        return ['success' => false, 'error' => 'Usuario no encontrado'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getUsuarioByEmail: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function getUsuarioByToken($token)
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/usuarios/token/' . $token);
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? null];
        }
        return ['success' => false, 'error' => 'Token no encontrado'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getUsuarioByToken: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function createUsuario($data)
{
    try {
        $response = Http::timeout($this->timeout)
            ->post($this->baseUrl . '/usuarios', $data);
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? null];
        }
        return ['success' => false, 'error' => 'Error al crear usuario'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::createUsuario: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function updateUsuario($id, $data)
{
    try {
        $response = Http::timeout($this->timeout)
            ->put($this->baseUrl . "/usuarios/{$id}", $data);
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? null];
        }
        return ['success' => false, 'error' => 'Error al actualizar usuario'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::updateUsuario: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}



// ============= MÉTODOS DE ML =============

public function getMLEstadisticas()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/ml/estadisticas');

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }

        return ['success' => false, 'error' => 'Modelo no disponible'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getMLEstadisticas: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function getClientesFrecuentes()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/ml/clientes-frecuentes');

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }

        return ['success' => false, 'error' => 'Datos no disponibles'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getClientesFrecuentes: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function getProductosMes()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/ml/productos-mes');

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }

        return ['success' => false, 'error' => 'Datos no disponibles'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getProductosMes: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function getPrediccionSemana()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/ml/prediccion-semana');

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }

        return ['success' => false, 'error' => 'Datos no disponibles'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getPrediccionSemana: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

public function predecirVenta($cantidad, $precio)
{
    try {
        $response = Http::timeout(120) // Spark puede tardar en iniciar
            ->post($this->baseUrl . '/ml/predecir', [
                'cantidad' => (float) $cantidad,
                'precio'   => (float) $precio,
            ]);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }

        $body = $response->json();
        $errorMsg = $body['detail'] ?? $body['error'] ?? 'Error al predecir (HTTP ' . $response->status() . ')';
        return ['success' => false, 'error' => $errorMsg];
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        Log::error('PythonApiService::predecirVenta (conexión): ' . $e->getMessage());
        return ['success' => false, 'error' => 'No se pudo conectar con la API de predicción. ¿Está corriendo el servidor Python?'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::predecirVenta: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


public function getPrediccionInsumos()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/ml/prediccion-insumos');
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }
        return ['success' => false, 'error' => 'Datos no disponibles'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getPrediccionInsumos: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


public function getClasificacionInsumos()
{
    try {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/ml/clasificacion-insumos');
        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data'] ?? []];
        }
        return ['success' => false, 'error' => 'Datos no disponibles'];
    } catch (\Exception $e) {
        Log::error('PythonApiService::getClasificacionInsumos: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


}
