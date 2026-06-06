<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Inventario;
use App\Services\PythonApiService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProductosController extends Controller
{
    protected $pythonApi;

    public function __construct(PythonApiService $pythonApi)
    {
        $this->middleware('auth:usuarios');
        $this->pythonApi = $pythonApi;
    }

    public function crear()
    {
        $user = auth()->guard('usuarios')->user();
        $insumos = \App\Models\Insumo::orderBy('nombre')->get();
        return view('productos.crear', compact('user', 'insumos'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|image|max:2048',

            'insumos' => 'required|array|min:1',
            'insumos.*' => 'string',
        ]);

        // Preparar datos para la API Python
        $imagePath = null;
        if ($request->hasFile('imagen')) {
            $imagePath = $request->imagen->store('productos', 'public');
        }

        $data = [
            'nombre' => $request->nombre,
            'precio' => (float)$request->precio,
            'descripcion' => $request->descripcion,
            'imagen' => $imagePath,
            'fecha_actualizacion' => now()->toIso8601String(),
            'insumos' => $request->insumos,

        ];

        // Enviar ÚNICAMENTE a la API Python
        $response = $this->pythonApi->createProducto($data);

        if ($response['success']) {
            return redirect()->back()->with('success', 'Producto creado con éxito');
        } else {
            Log::error('Error al crear producto en API Python: ' . ($response['error'] ?? 'Unknown error'));
            return redirect()->back()->with('error', 'Error al crear producto: ' . ($response['error'] ?? 'Error desconocido'));
        }
    }

    public function leer()
    {
        $user = auth()->guard('usuarios')->user();
        
        // Obtener productos ÚNICAMENTE desde la API Python
        $response = $this->pythonApi->getProductos();

        if ($response['success']) {
            // Convertir los datos de la API a objetos stdClass para compatibilidad con la vista
            $productos = collect($response['data'])->map(function($item) {
                return (object) [
                    '_id' => $item['_id'] ?? null,
                    'nombre' => $item['nombre'] ?? '',
                    'precio' => $item['precio'] ?? 0,
                    'descripcion' => $item['descripcion'] ?? '',
                    'imagen' => $item['imagen'] ?? null,
                    'insumos' => $item['insumos'] ?? [],
                    'insumos_cantidad' => $item['insumos_cantidad'] ?? [],
                ];
            });

            
            return view('productos.leer', compact('productos', 'user'));
        } else {
            Log::error('Error al obtener productos de API Python: ' . ($response['error'] ?? 'Unknown error'));
            return back()->with('error', 'No se pudieron cargar los productos. Verifica que la API Python esté activa.');
        }
    }

    public function eliminar()
    {
        $user = auth()->guard('usuarios')->user();
        
        // Obtener productos ÚNICAMENTE desde la API Python
        $response = $this->pythonApi->getProductos();

        if ($response['success']) {
            // Convertir los datos de la API a objetos stdClass
            $productos = collect($response['data'])->map(function($item) {
                return (object) [
                    '_id' => $item['_id'] ?? null,
                    'nombre' => $item['nombre'] ?? '',
                    'precio' => $item['precio'] ?? 0,
                    'descripcion' => $item['descripcion'] ?? '',
                    'imagen' => $item['imagen'] ?? null,
                ];
            });
            
            return view('productos.eliminar', compact('productos', 'user'));
        } else {
            Log::error('Error al obtener productos de API Python');
            return back()->with('error', 'No se pudieron cargar los productos');
        }
    }

    // NUEVO MÉTODO: Ver inventario
    public function inventario()
    {
        $user = auth()->guard('usuarios')->user();

        $response = $this->pythonApi->getInventario();

        if (!$response['success']) {
            Log::error('Error al obtener inventario de API Python');
            return back()->with('error', 'No se pudo cargar el inventario.');
        }
        
        // Obtener estadísticas
        $statsResponse = $this->pythonApi->getInventarioEstadisticas();
        $stats = $statsResponse['success'] ? $statsResponse['data'] : [
            'total_stock' => 0,
            'total_productos' => 0,
            'productos_bajo_stock' => 0
        ];
        
        // Convertir los datos de la API a objetos para la vista
        $productos_con_inventario = collect($response['data'])->map(function($item) {
            // Crear objeto producto
            $producto = (object) [
                '_id' => $item['producto']['_id'] ?? null,
                'nombre' => $item['producto']['nombre'] ?? 'Sin nombre',
                'precio' => $item['producto']['precio'] ?? 0,
                'descripcion' => $item['producto']['descripcion'] ?? '',
                'imagen' => $item['producto']['imagen'] ?? null,
            ];
            
            // Crear objeto inventario
            $inventario = (object) [
                '_id' => $item['_id'] ?? null,
                'stock_actual' => $item['stock_actual'] ?? 0,
                'stock_minimo' => $item['stock_minimo'] ?? 0,
                'fecha_actualizacion' => $item['fecha_actualizacion'] ?? null,
                'bajo_stock' => ($item['stock_actual'] ?? 0) <= ($item['stock_minimo'] ?? 0)
            ];
            
            // Asignar inventario al producto
            $producto->inventario = $inventario;
            
            return $producto;
        })->toArray();
        
        $total_stock = $stats['total_stock'];
        $productos_bajo_stock = $stats['productos_bajo_stock'];
        
        return view('inventario.index', compact('productos_con_inventario', 'user', 'total_stock', 'productos_bajo_stock'));

    }

    // NUEVO MÉTODO: Actualizar stock
    public function actualizarStock(Request $request)
    {
        $request->validate([
            'producto_id' => 'required',
            'nuevo_stock' => 'required|integer|min:0',
            'motivo' => 'required|string'
        ]);

        // Actualizar en la API Python
        $data = [
            'stock_actual' => (int)$request->nuevo_stock,
            'motivo' => $request->motivo
        ];
        
        $response = $this->pythonApi->updateInventarioByProducto($request->producto_id, $data);

        if ($response['success']) {
            return back()->with('success', 'Stock actualizado correctamente');
        } else {
            Log::error('Error al actualizar stock en API Python: ' . ($response['error'] ?? 'Unknown error'));
            return back()->with('error', 'Error al actualizar stock: ' . ($response['error'] ?? 'Error desconocido'));
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->input('IdProducto');

        // Eliminar ÚNICAMENTE en la API Python (que maneja MongoDB Atlas)
        $response = $this->pythonApi->deleteProducto($id);

        if ($response['success']) {
            return redirect()->back()->with('success', 'Producto eliminado con éxito');
        } else {
            Log::error('Error al eliminar producto en API Python: ' . ($response['error'] ?? 'Unknown error'));
            return redirect()->back()->with('error', 'Error al eliminar producto: ' . ($response['error'] ?? 'Error desconocido'));
        }
    }

public function update(Request $request, $id)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'precio' => 'required|numeric',
        'descripcion' => 'required|string',
        'imagen' => 'nullable|image|max:2048',
    ]);

    $data = [
        'nombre' => $request->nombre,
        'precio' => (float)$request->precio,
        'descripcion' => $request->descripcion,
        'insumos' => $request->input('insumos', []),
        'insumos_cantidad' => $request->input('insumos_cantidad', []),
    ];

    if ($request->hasFile('imagen')) {
        $imagePath = $request->imagen->store('productos', 'public');
        $data['imagen'] = $imagePath;
    }

    $response = $this->pythonApi->updateProducto($id, $data);

    if ($response['success']) {
        return redirect()->route('productos.leer')->with('success', 'Producto actualizado con éxito');
    } else {
        Log::error('Error al actualizar producto en API Python: ' . ($response['error'] ?? 'Unknown error'));
        return redirect()->back()->with('error', 'Error al actualizar producto: ' . ($response['error'] ?? 'Error desconocido'));
    }
}

public function editarVista($id)
{
    $user = auth()->guard('usuarios')->user();
    $response = $this->pythonApi->getProducto($id);

    if (!$response['success']) {
        return back()->with('error', 'Producto no encontrado');
    }

    $data = $response['data'];
    $producto = (object) [
        '_id'              => $data['_id'] ?? null,
        'nombre'           => $data['nombre'] ?? '',
        'precio'           => $data['precio'] ?? 0,
        'descripcion'      => $data['descripcion'] ?? '',
        'imagen'           => $data['imagen'] ?? null,
        'insumos'          => $data['insumos'] ?? [],
        'insumos_cantidad' => $data['insumos_cantidad'] ?? [],
    ];
    $insumos = \App\Models\Insumo::orderBy('nombre')->get();
    return view('productos.editar', compact('producto', 'user', 'insumos'));

}


}