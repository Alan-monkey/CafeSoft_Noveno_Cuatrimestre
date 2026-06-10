<?php

use App\Http\Controllers\LibrosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RegistrarseController;
use App\Http\Controllers\VentasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\InsumosController;
use Illuminate\Support\Facades\Http;


Route::get('/', function () {
    return view('auth.login');
});

// Rutas públicas (sin autenticación)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Rutas para recuperación de contraseña
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.update');
Route::get('/password/show-link/{token}', [PasswordResetController::class, 'showGeneratedLink'])->name('password.show.link');

// Rutas para invitados (tipo 1)
Route::middleware(['auth:usuarios', 'check.user.type:1'])->group(function () {
    Route::get('/inicio', [LibrosController::class, 'inicioInv'])->name('inicio.invitado');
});

// Rutas compartidas de carrito (para ambos tipos de usuarios)
Route::middleware(['auth:usuarios'])->group(function () {
    Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');
    Route::post('/carrito/agregar/{id}', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::post('/carrito/actualizar/{id}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::get('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::get('/carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
    Route::get('/pago', [CarritoController::class, 'mostrarPago'])->name('carrito.mostrar-pago');
    Route::post('/carrito/procesar-pago', [CarritoController::class, 'procesarPago'])->name('carrito.procesar-pago');
    Route::get('/pago-exito', [CarritoController::class, 'pagoExito'])->name('carrito.pago-exito');
    Route::get('/descargar-ticket', [CarritoController::class, 'descargarTicket'])->name('carrito.descargar-ticket');
    Route::get('/ventas/ticket/{id}', [CarritoController::class, 'ticket'])->name('ventas.ticket');
});

// Rutas para empleados (tipo 0)
Route::middleware(['auth:usuarios', 'check.user.type:0'])->group(function () {
    // Rutas de libros para empleados
    Route::get('/libros/crear', [LibrosController::class, 'crear'])->name('libros.crear');
    Route::post('/libros/store', [LibrosController::class, 'store'])->name('libros.store');
    Route::get('/libros/leer', [LibrosController::class, 'leer'])->name('libros.leer');
    Route::put('/libros/{libro}', [LibrosController::class, 'update'])->name('libros.update');
    Route::get('/libros/eliminar', [LibrosController::class, 'eliminar'])->name('libros.eliminar');
    Route::post('/libros/destroy', [LibrosController::class, 'destroy'])->name('libros.destroy');
    Route::get('/libros/inicio', [LibrosController::class, 'inicio'])->name('libros.inicio');
    Route::get('/libros/consultar', [LibrosController::class, 'consultar'])->name('libros.consultar');
    
    // Rutas de productos para empleados
    Route::get('/productos/crear', [ProductosController::class, 'crear'])->name('productos.crear');
    Route::get('/productos/leer', [ProductosController::class, 'leer'])->name('productos.leer');
    Route::post('/productos/store', [ProductosController::class, 'store'])->name('productos.store');
    Route::put('/productos/{producto}', [ProductosController::class, 'update'])->name('productos.update');
    Route::get('/productos/eliminar', [ProductosController::class, 'eliminar'])->name('productos.eliminar');
    Route::post('/productos/destroy', [ProductosController::class, 'destroy'])->name('productos.destroy');
    Route::get('/productos/{id}/editar', [ProductosController::class, 'editarVista'])->name('productos.editar');

    // Rutas para backups (solo empleados)
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::get('/backups/create', [BackupController::class, 'createBackupForm'])->name('backups.create');
    Route::post('/backups', [BackupController::class, 'createBackup'])->name('backups.store');
    Route::get('/backups/download/{filename}', [BackupController::class, 'downloadBackup'])->name('backups.download');
    Route::delete('/backups/{filename}', [BackupController::class, 'deleteBackup'])->name('backups.delete');
    Route::get('/backups/restore', [BackupController::class, 'restoreBackupForm'])->name('backups.restore.form');
    Route::get('/backups/restore/{filename}', [BackupController::class, 'restoreBackupForm'])->name('backups.restore.form.withfile');
    Route::post('/backups/restore', [BackupController::class, 'restoreBackup'])->name('backups.restore');
    Route::get('/backups/test-restore/{filename}', [BackupController::class, 'testRestore'])->name('backups.test');
    Route::get('/backups/debug/{filename}', [BackupController::class, 'debugBackup'])->name('backups.debug');
    Route::get('/backups/verify', [BackupController::class, 'verifyRestore'])->name('backups.verify');
    Route::post('/backups/verify-password', [BackupController::class, 'verifyPassword'])->name('backups.verify-password');
    Route::post('/backups/verify-password-restore', [BackupController::class, 'verifyPasswordRestore'])->name('backups.verify-password-restore');
    Route::post('/backups/verify-password-download', [BackupController::class, 'verifyPasswordDownload'])->name('backups.verify-password-download');
    Route::post('/backups/verify-password-delete', [BackupController::class, 'verifyPasswordDelete'])->name('backups.verify-password-delete');
    
    // Rutas de ventas (solo empleados)
    Route::get('/ventas', [App\Http\Controllers\VentasController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/reportes', [App\Http\Controllers\VentasController::class, 'reportes'])->name('ventas.reportes');
    Route::get('/ventas/mesas/disponibles', [App\Http\Controllers\VentasController::class, 'mesasDisponibles'])->name('ventas.mesas.disponibles');
    Route::get('/ventas/{id}', [App\Http\Controllers\VentasController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/predecir', [VentasController::class, 'predecir'])->name('ventas.predecir');

    // Rutas de registro (solo empleados)
    Route::get('/libros/registrarse', [RegistrarseController::class, 'registrarse'])->name('libros.registrarse');
    Route::post('/libros/registrarse', [RegistrarseController::class, 'registrar'])->name('libros.registrar');


    // Rutas para insumos
    // Insumos
// Insumos / Inventario
    Route::get('/inventario/crear',       [InsumosController::class, 'create'])->name('insumos.create');
    Route::post('/inventario',            [InsumosController::class, 'store'])->name('insumos.store');
    Route::get('/inventario',             [InsumosController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/{id}/editar', [InsumosController::class, 'edit'])->name('insumos.edit');
    Route::put('/inventario/{id}',        [InsumosController::class, 'update'])->name('insumos.update');
    Route::delete('/inventario/{id}',     [InsumosController::class, 'destroy'])->name('insumos.destroy');
    Route::get('/inventario/estadisticas', [InsumosController::class, 'estadisticas'])->name('inventario.estadisticas');


    
Route::get('/analytics-insumos', function () {
    $mapreduce    = [];
    $predicciones = [];
    $clasificaciones = [];

    // ── MapReduce: pide datos frescos a FastAPI ──────────────────────
    try {
        $response = Http::timeout(10)
                        ->get('http://127.0.0.1:8000/analytics/mapreduce-insumos');

        if ($response->successful()) {
            $mapreduce = $response->json('data') ?? [];
        }
    } catch (\Exception $e) {
        // Fallback: leer el JSON en disco si la API no responde
        $path = base_path('api/mapreduce_insumos.json');
        if (file_exists($path)) {
            $mapreduce = json_decode(file_get_contents($path), true) ?? [];
        }
    }

    // ── Predicciones ML (si ya las tienes) ──────────────────────────
    $pathML = base_path('api/predicciones.json');
    if (file_exists($pathML)) {
        $predicciones = json_decode(file_get_contents($pathML), true) ?? [];
    }

    // ── Clasificaciones árbol de decisión ───────────────────────────
    $pathClasif = base_path('api/clasificaciones.json');
    if (file_exists($pathClasif)) {
        $clasificaciones = json_decode(file_get_contents($pathClasif), true) ?? [];
    }

    return view('inventario.estadisticas', compact(
        'mapreduce',
        'predicciones',
        'clasificaciones'
    ));
});

Route::get('/analytics-kmeans', function () {

    $path = base_path('api/kmeans_productos.json');

    $kmeans = [];

    if(file_exists($path)){
        $kmeans = json_decode(
            file_get_contents($path),
            true
        ) ?? [];
    }

    return view(
        'analytics.kmeans-productos',
        compact('kmeans')
    );
});


});