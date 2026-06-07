<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Venta;
use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PythonApiService;
use Illuminate\Support\Facades\Cache;

class CarritoController extends Controller
{
    protected $pythonApi;

    public function __construct(PythonApiService $pythonApi)
    {
        $this->middleware('auth:usuarios');
        $this->pythonApi = $pythonApi;
    }

public function agregar(Request $request, $id)
{
    $productoResponse = $this->pythonApi->getProducto($id);

    if (!$productoResponse['success']) {
        return back()->with('error', 'Producto no encontrado');
    }

    $producto = (object) $productoResponse['data'];

    $carrito = session()->get('carrito', []);

    if (isset($carrito[$id])) {
        $carrito[$id]['cantidad'] += 1;
    } else {
        $carrito[$id] = [
            'id' => $id,
            'nombre' => $producto->nombre,
            'precio' => $producto->precio,
            'imagen' => $producto->imagen ?? null,
            'cantidad' => 1,
        ];
    }

    session()->put('carrito', $carrito);

    return back()->with('success', 'Producto agregado al carrito');
}

    public function ver()
{
    $carrito = session()->get('carrito', []);

    $productosResponse = Cache::remember('productos_lista', 60, function () {
        return $this->pythonApi->getProductos();
    });

    if (!$productosResponse['success']) {
        return back()->with('error', 'No se pudieron cargar los productos');
    }

    $inventarioResponse = Cache::remember('inventario_lista', 60, function () {
        return $this->pythonApi->getInventario();
    });

    $inventarioMap = [];
    if ($inventarioResponse['success']) {
        foreach ($inventarioResponse['data'] as $inv) {
            $inventarioMap[$inv['producto_id']] = $inv['stock_actual'] ?? 0;
        }
    }

    $productos = collect($productosResponse['data'])->map(function ($item) use ($inventarioMap) {
        return (object) [
            '_id'         => $item['_id'] ?? null,
            'nombre'      => $item['nombre'] ?? '',
            'precio'      => $item['precio'] ?? 0,
            'descripcion' => $item['descripcion'] ?? '',
            'imagen'      => $item['imagen'] ?? null,
            'stock'       => $inventarioMap[$item['_id']] ?? 0,
        ];
    });

    $user = auth()->guard('usuarios')->user();

    if ($user->user_tipo == 0) {
        return view('carrito.ver', compact('carrito', 'productos', 'user'));
    } else {
        return view('carrito.verInv', compact('carrito', 'productos', 'user'));
    }
}


    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1'
        ]);

        $carrito = session()->get('carrito', []);

        if (!isset($carrito[$id])) {
            return back()->with('error', 'Producto no encontrado en el carrito');
        }

        $carrito[$id]['cantidad'] = $request->cantidad;

        session()->put('carrito', $carrito);

        return back()->with('success', 'Cantidad actualizada');
    }

    public function eliminar($id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
            return back()->with('success', 'Producto eliminado del carrito');
        }

        return back()->with('error', 'Producto no encontrado en el carrito');
    }

    public function mostrarPago()
    {
        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return redirect()->route('carrito.ver')->with('error', 'El carrito está vacío');
        }

        $total = $this->calcularTotal($carrito);
        $user = auth()->guard('usuarios')->user();

        return view('carrito.pago', compact('total', 'carrito', 'user'));
    }

    public function procesarPago(Request $request)
    {
        $request->validate([
            'efectivo_recibido' => 'nullable|numeric|min:0',
            'mesa' => 'required|integer|min:1|max:10',
            'puntos_a_usar' => 'nullable|numeric|min:0'
        ]);

        $carrito = session()->get('carrito', []);

        if (empty($carrito)) {
            return redirect()->back()->with('error', 'El carrito está vacío');
        }

        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        $usuario = auth()->guard('usuarios')->user();
        $puntosAUsar = 0;
        $descuento = 0;

        if ($usuario && $usuario->user_tipo == 1) {
            $puntosAUsar = floatval($request->input('puntos_a_usar', 0));

            if ($puntosAUsar > ($usuario->puntos ?? 0)) {
                return redirect()->back()->with('error', 'No tienes suficientes puntos');
            }

            if ($puntosAUsar > $total) {
                return redirect()->back()->with('error', 'No se puede usar mas puntos del total de la compra');
            }

            $descuento = $puntosAUsar;
        } elseif ($request->input('puntos_a_usar', 0) > 0) {
            return redirect()->back()->with('error', 'Solo los clientes pueden usar puntos');
        }

        $totalFinal = $total - $descuento;
        $efectivoRecibido = floatval($request->efectivo_recibido ?? 0);
        $mesa = intval($request->mesa);

        if ($totalFinal > 0 && $efectivoRecibido < $totalFinal) {
            $faltan = number_format($totalFinal - $efectivoRecibido, 2);
            return redirect()->back()->with('error', 'El efectivo recibido es insuficiente. Faltan $' . $faltan);
        }

        $cambio = $efectivoRecibido - $totalFinal;

        // PASO 1: Verificar que los productos existen
        foreach ($carrito as $item) {
            $productoResponse = $this->pythonApi->getProducto($item['id']);
            if (!$productoResponse['success']) {
                return redirect()->back()->with('error', 'Producto no encontrado: ' . $item['nombre']);
            }
        }

        try {
            // PASO 2: Crear la venta
            $venta = new Venta();
            $venta->folio = Venta::generarFolio();
            $venta->productos = array_map(function ($item) {
                return [
                    'producto_id' => $item['id'],
                    'nombre' => $item['nombre'],
                    'precio' => $item['precio'],
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $item['precio'] * $item['cantidad']
                ];
            }, $carrito);
            $venta->total = $totalFinal;
            $venta->subtotal = $total;
            $venta->descuento_puntos = $descuento;
            $venta->puntos_usados = $puntosAUsar;
            $venta->efectivo_recibido = $efectivoRecibido;
            $venta->cambio = $cambio;
            $venta->mesa = $mesa;
            $venta->fecha_venta = now();
            $venta->estado = 'completada';
            $venta->usuario_id = auth()->guard('usuarios')->id();

            $guardado = $venta->save();

            if (!$guardado) {
                return redirect()->back()->with('error', 'No se pudo guardar la venta');
            }

            $ventaId = (string) $venta->_id;

            // PASO 3: Puntos (solo clientes)
            // PASO 3: Puntos (solo clientes)
            if ($usuario && $usuario->user_tipo == 1) {
                $puntosGanados = floor($totalFinal / 60) * 15;
                $nuevosPuntos = ($usuario->puntos ?? 0) - $puntosAUsar + $puntosGanados;

                $this->pythonApi->updateUsuario((string) $usuario->_id, [
                    'puntos' => $nuevosPuntos
                ]);

                $venta->puntos_ganados = $puntosGanados;
                $venta->save();
            }


            // PASO 4: Descontar insumos por cada producto vendido
            foreach ($carrito as $item) {
                $productoResponse = $this->pythonApi->getProducto($item['id']);
                if (!$productoResponse['success']) continue;

                $productoData    = $productoResponse['data'];
                $insumos         = $productoData['insumos'] ?? [];
                $insumosCantidad = $productoData['insumos_cantidad'] ?? [];
                $cantidadVendida = $item['cantidad'];

                foreach ($insumos as $insumoId) {
                    $cantidadPorUnidad = floatval($insumosCantidad[$insumoId] ?? 0);
                    if ($cantidadPorUnidad <= 0) continue;

                    $insumo = \App\Models\Insumo::find($insumoId);
                    if (!$insumo) continue;

                    $nuevaCantidad = floatval($insumo->cantidad) - ($cantidadPorUnidad * $cantidadVendida);
                    $insumo->cantidad = max(0, $nuevaCantidad);
                    $insumo->save();

                    Log::info("Insumo {$insumo->nombre}: -{$cantidadPorUnidad} x {$cantidadVendida} = nuevo stock {$insumo->cantidad}");
                }
            }


            // Guardar datos en sesión
            session()->put('pago_total', $totalFinal);
            session()->put('pago_efectivo', $efectivoRecibido);
            session()->put('pago_cambio', $cambio);
            session()->put('pago_fecha', now());
            session()->put('pago_carrito', $carrito);
            session()->put('pago_mesa', $mesa);
            session()->put('pago_folio', $venta->folio);

            session()->forget('carrito');

            return redirect()->route('ventas.ticket', $ventaId)
                ->with('success', 'Pago procesado correctamente. Mesa ' . $mesa . ' asignada.');

        } catch (\Exception $e) {
            Log::error('Error en procesarPago: ' . $e->getMessage() . ' linea ' . $e->getLine());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage() . ' en línea ' . $e->getLine());
        }
    }

    public function ticket($id)
    {
        $user = auth()->guard('usuarios')->user();
        $venta = Venta::find((string) $id);

        if (!$venta) {
            return redirect()->route('inicio')->with('error', 'Venta no encontrada');
        }

        return view('carrito.ticket', compact('venta', 'user'));
    }

    public function pagoExito()
    {
        $user = auth()->guard('usuarios')->user();

        $total = session()->get('pago_total', 0);
        $efectivo = session()->get('pago_efectivo', 0);
        $cambio = session()->get('pago_cambio', 0);
        $fecha = session()->get('pago_fecha', now());
        $carrito = session()->get('pago_carrito', []);
        $mesa = session()->get('pago_mesa', 0);
        $folio = session()->get('pago_folio', '');

        if ($total == 0 || empty($carrito)) {
            return redirect()->route('carrito.ver')->with('error', 'No hay pago reciente');
        }

        return view('carrito.pago-exito', compact('total', 'efectivo', 'cambio', 'fecha', 'carrito', 'user', 'mesa', 'folio'));
    }

    public function descargarTicket()
    {
        $carrito = session()->get('pago_carrito', []);
        $total = session()->get('pago_total', 0);
        $efectivo = session()->get('pago_efectivo', 0);
        $cambio = session()->get('pago_cambio', 0);
        $fecha = session()->get('pago_fecha', now());
        $mesa = session()->get('pago_mesa', 0);
        $folio = session()->get('pago_folio', '');

        if (empty($carrito)) {
            $carrito = ['ejemplo' => ['nombre' => 'Producto de Ejemplo', 'precio' => 100, 'cantidad' => 1]];
            $total = 100;
            $efectivo = 150;
            $cambio = 50;
            $mesa = 1;
            $folio = 'V' . date('Ymd') . '0001';
        }

        $pedido = [
            'fecha' => $fecha instanceof \DateTime ? $fecha->format('d/m/Y H:i:s') : date('d/m/Y H:i:s', strtotime($fecha)),
            'numero_pedido' => $folio ?: 'PED-' . time() . '-' . rand(1000, 9999),
            'total' => $total,
            'efectivo' => $efectivo,
            'cambio' => $cambio,
            'mesa' => $mesa
        ];

        $usuario = auth()->guard('usuarios')->user();

        $pdf = Pdf::loadView('pdf.ticket', [
            'pedido' => $pedido,
            'carrito' => $carrito,
            'total' => $total,
            'efectivo' => $efectivo,
            'cambio' => $cambio,
            'usuario' => $usuario,
            'mesa' => $mesa
        ]);

        return $pdf->download('ticket-' . $pedido['numero_pedido'] . '.pdf');
    }

    private function calcularTotal($carrito)
    {
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return $total;
    }

    public function vaciar()
    {
        session()->forget('carrito');
        return redirect()->route('carrito.ver')->with('success', 'Carrito vaciado');
    }
}
