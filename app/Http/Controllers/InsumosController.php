<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insumo;
use App\Services\PythonApiService;


class InsumosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:usuarios');
    }

    public function index()
    {
        $insumos         = Insumo::orderBy('id_insumo', 'asc')->get();
        $total_insumos   = $insumos->count();
        $stock_bajo      = $insumos->filter(fn($i) => $i->cantidad <= $i->cantidad_minima)->count();
        $por_vencer = $insumos->filter(function($i) {
            if ($i->caducidad === 'NA') return false;
            try {
                $cad = \Carbon\Carbon::parse($i->caducidad);
                return $cad->diffInDays(now()) <= 7 && $cad->isFuture();
            } catch (\Exception $e) {
                return false;
            }
        })->count();

        return view('inventario.index', compact('insumos', 'total_insumos', 'stock_bajo', 'por_vencer'));
    }


    public function create()
    {
        return view('insumos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:100',
            'tipo'            => 'required|in:piezas,gramos,litros',
            'cantidad'        => 'required|numeric|min:0',
            'cantidad_minima' => 'required|numeric|min:0',
            'caducidad'       => 'required',
            'proveedor'       => 'required|string|max:100',
            'precio_unitario' => 'required|numeric|min:0',
        ]);
        if ($request->caducidad !== 'NA' && !strtotime($request->caducidad)) {
           return back()->withInput()
                ->withErrors(['caducidad' => 'Ingresa una fecha válida o escribe NA.']);
        }
        // Validar nombre único
        if (Insumo::where('nombre', $request->nombre)->exists()) {
            return back()->withInput()
                ->withErrors(['nombre' => 'Ya existe un insumo con ese nombre.']);
        }

        Insumo::create([
            'id_insumo'       => Insumo::generarId(),
            'nombre'          => $request->nombre,
            'tipo'            => $request->tipo,
            'cantidad'        => $request->cantidad,
            'cantidad_minima' => $request->cantidad_minima,
            'caducidad'       => $request->caducidad,
            'proveedor'       => $request->proveedor,
            'precio_unitario' => $request->precio_unitario,
        ]);

        return redirect()->route('inventario.index')
            ->with('success', 'Insumo registrado correctamente.');
    }

    public function edit($id)
    {
        $insumo = Insumo::where('id_insumo', (int)$id)->firstOrFail();
        return view('insumos.edit', compact('insumo'));
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::where('id_insumo', (int)$id)->firstOrFail();

        $request->validate([
            'nombre'          => 'required|string|max:100',
            'tipo'            => 'required|in:piezas,gramos,litros',
            'cantidad'        => 'required|numeric|min:0',
            'cantidad_minima' => 'required|numeric|min:0',
            'caducidad'       => 'required',
            'proveedor'       => 'required|string|max:100',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        if ($request->caducidad !== 'NA' && !strtotime($request->caducidad)) {
            return back()->withInput()
                ->withErrors(['caducidad' => 'Ingresa una fecha válida o escribe NA.']);
        }


        // Validar nombre único excluyendo el actual
        if (Insumo::where('nombre', $request->nombre)
                  ->where('id_insumo', '!=', (int)$id)
                  ->exists()) {
            return back()->withInput()
                ->withErrors(['nombre' => 'Ya existe un insumo con ese nombre.']);
        }

        $insumo->update([
            'nombre'          => $request->nombre,
            'tipo'            => $request->tipo,
            'cantidad'        => $request->cantidad,
            'cantidad_minima' => $request->cantidad_minima,
            'caducidad'       => $request->caducidad,
            'proveedor'       => $request->proveedor,
            'precio_unitario' => $request->precio_unitario,
        ]);

        return redirect()->route('inventario.index')
            ->with('success', 'Insumo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $insumo = Insumo::where('id_insumo', (int)$id)->firstOrFail();
        $insumo->delete();

        return redirect()->route('inventario.index')
            ->with('success', 'Insumo eliminado correctamente.');
    }

    public function estadisticas()
{
    $user = auth()->guard('usuarios')->user();
    $pythonApi = app(PythonApiService::class);
    $response = $pythonApi->getPrediccionInsumos();
    $predicciones = $response['success'] ? $response['data'] : [];
    return view('inventario.estadisticas', compact('predicciones', 'user'));
}

}
