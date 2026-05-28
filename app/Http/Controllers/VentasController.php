<?php
// app/Http/Controllers/VentasController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;

class VentasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:usuarios');
    }

    public function index()
    {
        $user = auth()->guard('usuarios')->user();
        $ventas = Venta::orderBy('created_at', 'desc')->paginate(15);
        
        return view('ventas.index', compact('ventas', 'user'));
    }

    public function show($id)
    {
        $user = auth()->guard('usuarios')->user();
        $venta = Venta::find($id);
        
        if (!$venta) {
            return redirect()->route('ventas.index')->with('error', 'Venta no encontrada');
        }
        
        return view('ventas.show', compact('venta', 'user'));
    }

    public function mesasDisponibles()
    {
        // Obtener mesas ocupadas en las últimas 2 horas
        $mesasOcupadas = Venta::where('created_at', '>=', now()->subHours(2))
            ->where('estado', '!=', 'completada')
            ->pluck('mesa')
            ->toArray();

        $mesas = [];
        for ($i = 1; $i <= 10; $i++) {
            $mesas[] = [
                'numero' => $i,
                'disponible' => !in_array($i, $mesasOcupadas)
            ];
        }

        return response()->json($mesas);
    }



public function reportes()
{
    $user = auth()->guard('usuarios')->user();

    // Ventas del día
    $ventasHoy = Venta::whereDate('created_at', today())->get();
    $totalHoy  = $ventasHoy->sum('total');

    // Productos más vendidos
    $productosVendidos = [];
    foreach ($ventasHoy as $venta) {
        foreach ($venta->productos as $producto) {
            $nombre = $producto['nombre'];
            $productosVendidos[$nombre] = ($productosVendidos[$nombre] ?? 0) + $producto['cantidad'];
        }
    }
    arsort($productosVendidos);
    $masVendidos = array_slice($productosVendidos, 0, 5, true);

    // ML: estadísticas del modelo
    $pythonApi   = app(\App\Services\PythonApiService::class);
    $mlResponse  = $pythonApi->getMLEstadisticas();
    $mlStats     = $mlResponse['success'] ? $mlResponse['data'] : null;

    $clientesResponse = $pythonApi->getClientesFrecuentes();
    $clientesFrecuentes = $clientesResponse['success'] ? $clientesResponse['data'] : [];

    $productosMesResponse = $pythonApi->getProductosMes();
    $productosMes = $productosMesResponse['success'] ? $productosMesResponse['data'] : [];

    $prediccionResponse = $pythonApi->getPrediccionSemana();
    $prediccionSemana = $prediccionResponse['success'] ? $prediccionResponse['data'] : null;

    return view('ventas.reportes', compact('user', 'ventasHoy', 'totalHoy', 'masVendidos', 'mlStats', 'clientesFrecuentes', 'productosMes', 'prediccionSemana'));
}

public function predecir(Request $request)
{
    $request->validate([
        'cantidad' => 'required|numeric|min:1',
        'precio'   => 'required|numeric|min:1',
    ]);

    $pythonApi = app(\App\Services\PythonApiService::class);
    $resultado = $pythonApi->predecirVenta($request->cantidad, $request->precio);

    return response()->json($resultado);
}

}