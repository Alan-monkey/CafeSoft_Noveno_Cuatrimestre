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
        <div class="coffee-bean bean-1"></div>
        <div class="coffee-bean bean-2"></div>
        <div class="particle particle-1"></div>
    </div>

    <div class="container py-4">

        <!-- Header -->
        <div class="inventario-header">
            <div class="header-icon"><i class="fas fa-chart-line"></i></div>
            <div class="header-title">
                <h4><i class="fas fa-brain"></i> Estadísticas ML — Insumos</h4>
                <p>Regresión lineal: predicción de consumo semanal</p>
            </div>
            <div class="coffee-decoration-header">
                <span>📊</span><span>🤖</span><span>☕</span>
            </div>
            <a href="{{ route('inventario.index') }}" class="btn-nuevo" style="background:rgba(255,255,255,.15);">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>

        <div class="inventario-card">

            @if(empty($predicciones))
                <div class="empty-state py-5 text-center">
                    <i class="fas fa-robot fa-4x mb-3" style="color:#d9b382;"></i>
                    <h5>No hay predicciones disponibles</h5>
                    <p style="color:#8B6B4F;">Ejecuta <code>python ml_trainer.py</code> desde la carpeta <code>api/</code> para generar los datos.</p>
                </div>
            @else

                <!-- Resumen alertas -->
                @php
                    $alertas = collect($predicciones)->where('alerta', true)->count();
                    $total   = count($predicciones);
                @endphp
                <div class="inventario-resumen mb-4">
                    <div class="resumen-card">
                        <i class="fas fa-flask"></i>
                        <span class="resumen-valor">{{ $total }}</span>
                        <span class="resumen-label">Insumos analizados</span>
                    </div>
                    <div class="resumen-card {{ $alertas > 0 ? 'danger' : '' }}">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="resumen-valor">{{ $alertas }}</span>
                        <span class="resumen-label">Stock insuficiente próx. semana</span>
                    </div>
                </div>


                <!-- Gráfica de dispersión: stock actual vs necesidad -->
                <div class="chart-wrapper mb-5">
                    <h5 class="chart-title">
                        <i class="fas fa-circle me-2"></i>
                        Stock actual vs Necesidad semanal (Regresion lineal)
                    </h5>
                    <canvas id="graficaDispersion" height="100"></canvas>
                </div>

                <!-- Tabla detalle -->
                <h5 class="chart-title mb-3">
                    <i class="fas fa-table me-2"></i> Detalle por insumo
                </h5>
                <div class="table-responsive">
                    <table class="table inventario-table">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Tipo</th>
                                <th class="text-center">Consumo mes</th>
                                <th class="text-center">Necesidad semana</th>
                                <th class="text-center">Stock actual</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($predicciones as $p)
                            <tr>
                                <td><strong style="color:#3E2723;">{{ $p['nombre'] }}</strong></td>
                                <td><span class="tipo-badge tipo-{{ $p['tipo'] }}">{{ ucfirst($p['tipo']) }}</span></td>
                                <td class="text-center">{{ number_format($p['consumo_mes'], 3) }}</td>
                                <td class="text-center">
                                    <span class="stock-badge {{ $p['alerta'] ? 'bajo' : 'normal' }}">
                                        {{ number_format($p['necesidad_semana'], 3) }}
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format($p['stock_actual'], 3) }}</td>
      
                                <td class="text-center">
                                    @if($p['alerta'])
                                        <span class="badge-estado agotado">⚠ Stock bajo</span>
                                    @else
                                        <span class="badge-estado normal">✓ Suficiente</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if(!empty($predicciones))
const datos = @json($predicciones);

const labels        = datos.map(d => d.nombre);
const consumoMes    = datos.map(d => d.consumo_mes);
const necesidadSem  = datos.map(d => d.necesidad_semana);
const stockActual   = datos.map(d => d.stock_actual);
const maxNecesidad = Math.max(...datos.map(d => d.necesidad_semana));
const coloresAlerta = datos.map(d => {
    const ratio = maxNecesidad > 0 ? d.necesidad_semana / maxNecesidad : 0;
    if (ratio >= 0.66) return 'rgba(220,53,69,0.85)';      // rojo — mucha necesidad
    if (ratio >= 0.33) return 'rgba(255,193,7,0.85)';       // amarillo — media
    return 'rgba(40,167,69,0.85)';                          // verde — poca necesidad
});

// Gráfica de barras

// Gráfica de dispersión
new Chart(document.getElementById('graficaDispersion'), {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Insumos (necesidad vs stock)',
            data: datos.map(d => ({ x: d.necesidad_semana, y: d.stock_actual, nombre: d.nombre })),
            backgroundColor: coloresAlerta,
            pointRadius: 8,
            pointHoverRadius: 12,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.raw.nombre} — Necesidad: ${ctx.raw.x.toFixed(3)}, Stock: ${ctx.raw.y.toFixed(3)}`
                }
            }
        },
        scales: {
            x: { title: { display: true, text: 'Necesidad semanal (predicción)' } },
            y: { title: { display: true, text: 'Stock actual' } }
        }
    }
});
@endif
</script>

<style>
.inventario-container { position:relative;min-height:100vh;background:linear-gradient(145deg,#faf0e6,#f5e6d3);font-family:'Poppins','Segoe UI',sans-serif;padding:20px 0;overflow-x:hidden; }
.coffee-elements { position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0; }
.coffee-cup { position:absolute;opacity:.15; }
.cup-1 { top:30px;left:30px;transform:scale(.7); }
.white-cup { background:linear-gradient(145deg,#fff,#f8f8f8) !important; }
.white-handle { border-color:#f0f0f0 !important;border-right:6px solid #fff !important; }
.cup-top { width:60px;height:15px;border-radius:50%;background:linear-gradient(145deg,#fff,#f0f0f0); }
.cup-body { width:50px;height:45px;border-radius:0 0 25px 25px;background:linear-gradient(145deg,#fff,#f5f5f5);top:-7px;position:relative; }
.cup-handle { width:18px;height:30px;border:5px solid #f0f0f0;border-left:none;border-radius:0 15px 15px 0;position:absolute;right:-15px;top:10px; }
.steam { position:absolute;background:rgba(255,255,255,.5);border-radius:50%;animation:steam 3s infinite; }
.s1{width:10px;height:10px;top:-15px;left:15px} .s2{width:8px;height:8px;top:-20px;left:25px;animation-delay:.5s} .s3{width:6px;height:6px;top:-18px;left:35px;animation-delay:1s}
@keyframes steam{0%,100%{transform:translateY(0) scale(1);opacity:.5}50%{transform:translateY(-10px) scale(1.2);opacity:.2}}
.coffee-bean{position:absolute;width:15px;height:7px;background:#8B4513;border-radius:50%;opacity:.1;animation:float 20s infinite linear;transform:rotate(45deg)}
.bean-1{top:15%;left:5%} .bean-2{bottom:20%;right:5%}
@keyframes float{from{transform:translateY(0) rotate(45deg);opacity:.1}to{transform:translateY(-100vh) rotate(405deg);opacity:0}}
.particle{position:absolute;width:3px;height:3px;background:rgba(139,69,19,.2);border-radius:50%;animation:particle-float 15s infinite linear}
.particle-1{top:20%;left:15%}
@keyframes particle-float{from{transform:translateY(0) scale(1);opacity:.3}to{transform:translateY(-100vh) scale(0);opacity:0}}

.inventario-header { background:linear-gradient(135deg,#8B4513,#A0522D);color:white;padding:25px 30px;display:flex;align-items:center;gap:20px;position:relative;overflow:hidden;border-radius:30px 30px 0 0;z-index:10;flex-wrap:wrap; }
.inventario-header::after { content:'';position:absolute;top:0;right:0;width:200px;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2));transform:skewX(-20deg) translateX(100px);animation:shine 3s infinite; }
@keyframes shine{0%{transform:skewX(-20deg) translateX(100px)}20%{transform:skewX(-20deg) translateX(-200px)}100%{transform:skewX(-20deg) translateX(-200px)}}
.header-icon { width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:2rem; }
.header-title h4 { margin:0;font-weight:700;font-size:1.5rem; }
.header-title p { margin:5px 0 0;opacity:.9;font-size:.9rem; }
.coffee-decoration-header { margin-left:auto;font-size:1.5rem; }
.coffee-decoration-header span { margin:0 5px;animation:bounce 2s infinite;display:inline-block; }
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}

.btn-nuevo { background:rgba(255,255,255,.2);backdrop-filter:blur(5px);color:white;border:1px solid rgba(255,255,255,.3);padding:10px 20px;border-radius:50px;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;transition:all .3s ease; }
.btn-nuevo:hover { background:#D4AF37;border-color:#D4AF37;color:#2c1a0b;transform:translateY(-2px); }

.inventario-card { background:rgba(255,255,255,.98);border-radius:0 0 30px 30px;padding:30px;box-shadow:0 20px 40px rgba(139,69,19,.15);z-index:10;position:relative; }

.inventario-resumen { display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px; }
.resumen-card { background:linear-gradient(145deg,#fff,#f5f0eb);padding:25px;border-radius:20px;text-align:center;box-shadow:0 5px 15px rgba(0,0,0,.05);border:1px solid rgba(139,69,19,.1);transition:all .3s ease; }
.resumen-card:hover { transform:translateY(-5px);box-shadow:0 15px 30px rgba(139,69,19,.15); }
.resumen-card i { font-size:2.5rem;color:#8B4513;margin-bottom:15px; }
.resumen-card.danger i { color:#dc3545; }
.resumen-valor { display:block;font-size:2rem;font-weight:700;color:#3E2723; }
.resumen-label { color:#8B6B4F;font-size:.9rem;text-transform:uppercase;letter-spacing:.5px; }

.chart-wrapper { background:#faf5f0;border-radius:20px;padding:25px;border:1px solid #e8d5c0; }
.chart-title { color:#5D4037;font-weight:700;font-size:1.1rem;margin-bottom:15px; }

.inventario-table { width:100%;border-collapse:separate;border-spacing:0 10px; }
.inventario-table thead th { background:#f8f4f0;color:#5D4037;padding:15px;font-weight:600;border-radius:10px; }
.inventario-table tbody tr { background:white;border-radius:15px;box-shadow:0 3px 10px rgba(0,0,0,.03);transition:all .3s ease; }
.inventario-table tbody tr:hover { transform:translateY(-3px);box-shadow:0 10px 25px rgba(139,69,19,.15); }
.inventario-table tbody td { padding:15px;vertical-align:middle;border:none; }

.tipo-badge { padding:5px 12px;border-radius:20px;font-size:.82rem;font-weight:600; }
.tipo-piezas { background:#e3f2fd;color:#1565c0; }
.tipo-gramos { background:#e8f5e9;color:#2e7d32; }
.tipo-litros { background:#e8eaf6;color:#283593; }
.tipo-kilogramos { background:#fff8e1;color:#f57f17; }
.tipo-mililitros { background:#ede7f6;color:#4527a0; }

.stock-badge { display:inline-block;padding:6px 14px;border-radius:30px;font-weight:600;font-size:.9rem; }
.stock-badge.normal { background:linear-gradient(145deg,#d4edda,#c3e6cb);color:#155724; }
.stock-badge.bajo { background:linear-gradient(145deg,#fff3cd,#ffe69c);color:#856404; }

.badge-estado { display:inline-block;padding:5px 12px;border-radius:20px;font-size:.85rem;font-weight:600; }
.badge-estado.normal { background:#28a745;color:white; }
.badge-estado.agotado { background:#dc3545;color:white; }

.empty-state { text-align:center;padding:40px; }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

@endsection
