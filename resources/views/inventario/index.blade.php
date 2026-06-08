@extends('layouts.app')
@section('content')

<div class="inventario-container">
    <div class="coffee-elements">
        <div class="coffee-cup cup-1">
            <div class="cup-top white-cup"></div>
            <div class="cup-body white-cup"></div>
            <div class="cup-handle white-handle"></div>
            <div class="steam s1"></div><div class="steam s2"></div><div class="steam s3"></div>
        </div>
        <div class="coffee-cup cup-2">
            <div class="cup-top white-cup"></div>
            <div class="cup-body white-cup"></div>
            <div class="cup-handle white-handle"></div>
            <div class="steam s1"></div><div class="steam s2"></div><div class="steam s3"></div>
        </div>
        <div class="coffee-bean bean-1"></div>
        <div class="coffee-bean bean-2"></div>
        <div class="coffee-bean bean-3"></div>
        <div class="particle particle-1"></div>
        <div class="particle particle-2"></div>
    </div>

    <div class="container py-4">

        @if(session('success'))
        <div class="alert-success-custom">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
        @endif

        <!-- Header -->
        <div class="inventario-header">
            <div class="header-icon"><i class="fas fa-boxes"></i></div>
            <div class="header-title">
                <h4><i class="fas fa-coffee"></i> Control de Insumos</h4>
                <p>Gestión de insumos del almacén</p>
            </div>
            <div class="coffee-decoration-header">
                <span>📦</span><span>☕</span><span>🧪</span>
            </div>
            <a href="{{ route('insumos.create') }}" class="btn-nuevo">
                <i class="fas fa-plus me-2"></i> Nuevo Insumo
            </a>
            <a href="{{ route('inventario.estadisticas') }}" class="btn-nuevo" style="background:rgba(255,255,255,.15);">
                <i class="fas fa-chart-line me-2"></i> Estadísticas ML
            </a>
        </div>

        <!-- Tarjeta principal -->
        <div class="inventario-card">

            <!-- Resumen -->
            <div class="inventario-resumen">
                <div class="resumen-card">
                    <i class="fas fa-cubes"></i>
                    <span class="resumen-valor">{{ $total_insumos }}</span>
                    <span class="resumen-label">Total insumos</span>
                </div>
                <div class="resumen-card warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="resumen-valor">{{ $stock_bajo }}</span>
                    <span class="resumen-label">Stock bajo</span>
                </div>
                <div class="resumen-card danger">
                    <i class="fas fa-calendar-times"></i>
                    <span class="resumen-valor">{{ $por_vencer }}</span>
                    <span class="resumen-label">Por vencer (7 días)</span>
                </div>
            </div>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table inventario-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Mín.</th>
                            <th>Caducidad</th>
                            <th>Proveedor</th>
                            <th class="text-end">Precio unit.</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insumos as $insumo)
                        <tr>
                            <td data-label="#">
                                <span class="id-badge">{{ $insumo->id_insumo }}</span>
                            </td>
                            <td data-label="Nombre">
                                <strong style="color:#3E2723;">{{ $insumo->nombre }}</strong>
                            </td>
                            <td data-label="Tipo">
                                <span class="tipo-badge tipo-{{ $insumo->tipo }}">{{ ucfirst($insumo->tipo) }}</span>
                            </td>
                            <td data-label="Cantidad" class="text-center">
                                <span class="stock-badge {{ $insumo->cantidad <= $insumo->cantidad_minima ? 'bajo' : 'normal' }}">
                                    {{ $insumo->cantidad }}
                                </span>
                            </td>
                            <td data-label="Mín." class="text-center">{{ $insumo->cantidad_minima }}</td>
                            <td data-label="Caducidad">
                                @if($insumo->caducidad === 'NA')
                                    <span style="color:#8B6B4F;font-weight:600;">N/A</span>
                                @else
                                    @php $cad = \Carbon\Carbon::parse($insumo->caducidad); @endphp
                                    <span class="{{ $cad->isPast() ? 'text-danger fw-bold' : ($cad->diffInDays(now()) <= 7 ? 'text-warning fw-bold' : '') }}">
                                        {{ $cad->format('d/m/Y') }}
                                    </span>
                                @endif
                            </td>

                            <td data-label="Proveedor">{{ $insumo->proveedor }}</td>
                            <td data-label="Precio unit." class="text-end">
                                <span class="precio-cell">${{ number_format($insumo->precio_unitario, 2) }}</span>
                            </td>
                            <td data-label="Estado">
                                @if($insumo->caducidad !== 'NA' && \Carbon\Carbon::parse($insumo->caducidad)->isPast())
                                    <span class="badge-estado agotado">Vencido</span>
                                @elseif($insumo->cantidad == 0)
                                    <span class="badge-estado agotado">Sin stock</span>
                                @elseif($insumo->cantidad <= $insumo->cantidad_minima)
                                    <span class="badge-estado bajo">Stock bajo</span>
                                @else
                                    <span class="badge-estado normal">Normal</span>
                                @endif
                            </td>

                            <td data-label="Acciones" class="text-center">
                                <div class="acciones-group">
                                    <a href="{{ route('insumos.edit', $insumo->id_insumo) }}" class="btn-accion editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmarEliminar({{ $insumo->id_insumo }}, '{{ $insumo->nombre }}')" class="btn-accion eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-box-open fa-4x mb-3" style="color:#d9b382;"></i>
                                    <h5>No hay insumos registrados</h5>
                                    <a href="{{ route('insumos.create') }}" class="btn-nuevo mt-3">
                                        <i class="fas fa-plus me-2"></i> Registrar insumo
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal eliminar -->
<div id="modalEliminar" class="modal-confirm">
    <div class="modal-content" style="max-width:420px;">
        <div class="modal-header" style="background:linear-gradient(135deg,#dc3545,#c82333);">
            <div class="modal-icon"><i class="fas fa-trash-alt"></i></div>
            <h3>Eliminar insumo</h3>
        </div>
        <div class="modal-body text-center">
            <p>¿Seguro que deseas eliminar <strong id="nombreEliminar"></strong>?</p>
            <p class="text-muted" style="font-size:0.9rem;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="cerrarModal()"><i class="fas fa-times me-1"></i>Cancelar</button>
            <form id="formEliminar" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-confirmar" style="background:linear-gradient(145deg,#dc3545,#c82333);">
                    <i class="fas fa-trash me-1"></i>Eliminar
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id, nombre) {
    document.getElementById('nombreEliminar').textContent = nombre;
    document.getElementById('formEliminar').action = '/insumos/' + id;
    document.getElementById('modalEliminar').style.display = 'flex';
}
function cerrarModal() {
    document.getElementById('modalEliminar').style.display = 'none';
}
window.onclick = e => { if (e.target == document.getElementById('modalEliminar')) cerrarModal(); }
</script>

<style>
/* ===== CONTENEDOR PRINCIPAL ===== */
.inventario-container {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(145deg, #faf0e6, #f5e6d3);
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    padding: 20px 0;
    overflow-x: hidden;
}

/* ===== ELEMENTOS DECORATIVOS ===== */
.coffee-elements {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    pointer-events: none;
    z-index: 0;
}

.coffee-cup { position: absolute; opacity: .15; }
.cup-1 { top: 30px; left: 30px; transform: scale(.7); }
.cup-2 { bottom: 30px; right: 30px; transform: scale(.7) rotate(-10deg); }

.white-cup { background: linear-gradient(145deg, #fff, #f8f8f8) !important; }
.white-handle { border-color: #f0f0f0 !important; border-right: 6px solid #fff !important; }

.cup-top {
    width: 60px; height: 15px;
    border-radius: 50%;
    background: linear-gradient(145deg, #fff, #f0f0f0);
}
.cup-body {
    width: 50px; height: 45px;
    border-radius: 0 0 25px 25px;
    background: linear-gradient(145deg, #fff, #f5f5f5);
    top: -7px; position: relative;
}
.cup-handle {
    width: 18px; height: 30px;
    border: 5px solid #f0f0f0;
    border-left: none;
    border-radius: 0 15px 15px 0;
    position: absolute;
    right: -15px; top: 10px;
}

.steam {
    position: absolute;
    background: rgba(255,255,255,.5);
    border-radius: 50%;
    animation: steam 3s infinite;
}
.s1 { width: 10px; height: 10px; top: -15px; left: 15px; }
.s2 { width: 8px;  height: 8px;  top: -20px; left: 25px; animation-delay: .5s; }
.s3 { width: 6px;  height: 6px;  top: -18px; left: 35px; animation-delay: 1s; }

@keyframes steam {
    0%, 100% { transform: translateY(0) scale(1); opacity: .5; }
    50%       { transform: translateY(-10px) scale(1.2); opacity: .2; }
}

.coffee-bean {
    position: absolute;
    width: 15px; height: 7px;
    background: #8B4513;
    border-radius: 50%;
    opacity: .1;
    animation: float 20s infinite linear;
    transform: rotate(45deg);
}
.bean-1 { top: 15%; left: 5%; }
.bean-2 { bottom: 20%; right: 5%; }
.bean-3 { top: 40%; left: 8%; }

@keyframes float {
    from { transform: translateY(0) rotate(45deg); opacity: .1; }
    to   { transform: translateY(-100vh) rotate(405deg); opacity: 0; }
}

.particle {
    position: absolute;
    width: 3px; height: 3px;
    background: rgba(139,69,19,.2);
    border-radius: 50%;
    animation: particle-float 15s infinite linear;
}
.particle-1 { top: 20%; left: 15%; }
.particle-2 { top: 60%; right: 10%; }

@keyframes particle-float {
    from { transform: translateY(0) scale(1); opacity: .3; }
    to   { transform: translateY(-100vh) scale(0); opacity: 0; }
}

/* ===== ALERTA ===== */
.alert-success-custom {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    padding: 15px 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

/* ===== HEADER ===== */
.inventario-header {
    background: linear-gradient(135deg, #8B4513, #A0522D);
    color: white;
    padding: 25px 30px;
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    overflow: hidden;
    border-radius: 30px 30px 0 0;
    z-index: 10;
    flex-wrap: wrap;
}

.inventario-header::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 200px; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.2));
    transform: skewX(-20deg) translateX(100px);
    animation: shine 3s infinite;
}

@keyframes shine {
    0%   { transform: skewX(-20deg) translateX(100px); }
    20%  { transform: skewX(-20deg) translateX(-200px); }
    100% { transform: skewX(-20deg) translateX(-200px); }
}

.header-icon {
    width: 60px; height: 60px;
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.header-title h4 { margin: 0; font-weight: 700; font-size: 1.5rem; }
.header-title p  { margin: 5px 0 0; opacity: .9; font-size: .9rem; }

.coffee-decoration-header { margin-left: auto; font-size: 1.5rem; }
.coffee-decoration-header span {
    margin: 0 5px;
    animation: bounce 2s infinite;
    display: inline-block;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-5px); }
}

.btn-nuevo {
    background: rgba(255,255,255,.2);
    backdrop-filter: blur(5px);
    color: white;
    border: 1px solid rgba(255,255,255,.3);
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: all .3s ease;
}
.btn-nuevo:hover {
    background: #D4AF37;
    border-color: #D4AF37;
    color: #2c1a0b;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,.2);
}

/* ===== TARJETA PRINCIPAL ===== */
.inventario-card {
    background: rgba(255,255,255,.98);
    border-radius: 0 0 30px 30px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(139,69,19,.15);
    z-index: 10;
    position: relative;
}

/* ===== RESUMEN ===== */
.inventario-resumen {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.resumen-card {
    background: linear-gradient(145deg, #fff, #f5f0eb);
    padding: 25px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,.05);
    border: 1px solid rgba(139,69,19,.1);
    transition: all .3s ease;
}
.resumen-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(139,69,19,.15);
}
.resumen-card i        { font-size: 2.5rem; color: #8B4513; margin-bottom: 15px; }
.resumen-card.warning i { color: #ffc107; }
.resumen-card.danger i  { color: #dc3545; }

.resumen-valor {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #3E2723;
}
.resumen-label {
    color: #8B6B4F;
    font-size: .9rem;
    text-transform: uppercase;
    letter-spacing: .5px;
}

/* ===== TABLA ===== */
.inventario-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
}
.inventario-table thead th {
    background: #f8f4f0;
    color: #5D4037;
    padding: 15px;
    font-weight: 600;
    border-radius: 10px;
}
.inventario-table tbody tr {
    background: white;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,.03);
    transition: all .3s ease;
}
.inventario-table tbody tr:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(139,69,19,.15);
}
.inventario-table tbody td {
    padding: 15px;
    vertical-align: middle;
    border: none;
}

/* ===== BADGES ===== */
.id-badge {
    background: #f0e4d5;
    color: #8B4513;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 700;
    font-size: .85rem;
}

.tipo-badge   { padding: 5px 12px; border-radius: 20px; font-size: .82rem; font-weight: 600; }
.tipo-piezas  { background: #e3f2fd; color: #1565c0; }
.tipo-gramos  { background: #e8f5e9; color: #2e7d32; }
.tipo-litros  { background: #e8eaf6; color: #283593; }

.stock-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 30px;
    font-weight: 600;
    font-size: .9rem;
}
.stock-badge.normal { background: linear-gradient(145deg, #d4edda, #c3e6cb); color: #155724; }
.stock-badge.bajo   { background: linear-gradient(145deg, #fff3cd, #ffe69c); color: #856404; }

.precio-cell { font-weight: 600; color: #8B4513; }

.badge-estado { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: .85rem; font-weight: 600; }
.badge-estado.normal  { background: #28a745; color: white; }
.badge-estado.bajo    { background: #ffc107; color: #212529; }
.badge-estado.agotado { background: #dc3545; color: white; }

/* ===== ACCIONES ===== */
.acciones-group { display: flex; gap: 8px; justify-content: center; }

.btn-accion {
    width: 36px; height: 36px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all .3s ease;
    text-decoration: none;
    font-size: .9rem;
}
.btn-accion.editar  { background: linear-gradient(145deg, #8B4513, #A0522D); color: white; }
.btn-accion.eliminar { background: linear-gradient(145deg, #dc3545, #c82333); color: white; }
.btn-accion:hover   { transform: translateY(-2px); box-shadow: 0 5px 12px rgba(0,0,0,.2); }

.empty-state { text-align: center; padding: 40px; }

/* ===== MODAL ===== */
.modal-confirm {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,.5);
    backdrop-filter: blur(5px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: white;
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(0,0,0,.3);
    animation: slideUp .3s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(50px); }
    to   { opacity: 1; transform: translateY(0); }
}
.modal-header { color: white; padding: 20px; text-align: center; }
.modal-icon   { font-size: 2.5rem; margin-bottom: 10px; }
.modal-body   { padding: 25px; }
.modal-footer {
    padding: 20px 25px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    border-top: 1px solid #eee;
}

.btn-cancel {
    background: #f0f0f0;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    color: #666;
    cursor: pointer;
    transition: all .3s ease;
}
.btn-confirmar {
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    color: white;
    cursor: pointer;
    transition: all .3s;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .coffee-cup { display: none; }

    .inventario-header {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    .coffee-decoration-header { margin: 0 auto; }

    .inventario-table thead { display: none; }
    .inventario-table tbody tr  { display: block; margin-bottom: 15px; }
    .inventario-table tbody td  {
        display: block;
        text-align: right;
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
    }
    .inventario-table tbody td:before {
        content: attr(data-label);
        float: left;
        font-weight: 600;
        color: #8B4513;
    }
    .acciones-group { justify-content: flex-end; }
}

</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

@endsection
