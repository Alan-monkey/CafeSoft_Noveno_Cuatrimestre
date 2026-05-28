@extends('layouts.app')

@section('content')
<div class="pago-efectivo-container">
    <!-- Elementos decorativos de café - TAZAS BLANCAS -->
    <div class="coffee-elements">
        <div class="coffee-cup cup-1">
            <div class="cup-top white-cup"></div>
            <div class="cup-body white-cup"></div>
            <div class="cup-handle white-handle"></div>
            <div class="steam s1"></div>
            <div class="steam s2"></div>
            <div class="steam s3"></div>
        </div>
        <div class="coffee-cup cup-2">
            <div class="cup-top white-cup"></div>
            <div class="cup-body white-cup"></div>
            <div class="cup-handle white-handle"></div>
            <div class="steam s1"></div>
            <div class="steam s2"></div>
            <div class="steam s3"></div>
        </div>
        <div class="coffee-cup cup-3">
            <div class="cup-top white-cup"></div>
            <div class="cup-body white-cup"></div>
            <div class="cup-handle white-handle"></div>
            <div class="steam s1"></div>
            <div class="steam s2"></div>
            <div class="steam s3"></div>
        </div>
        <div class="coffee-bean bean-1"></div>
        <div class="coffee-bean bean-2"></div>
        <div class="coffee-bean bean-3"></div>
        <div class="coffee-bean bean-4"></div>
        <div class="coffee-bean bean-5"></div>
        <div class="particle particle-1"></div>
        <div class="particle particle-2"></div>
        <div class="particle particle-3"></div>
    </div>

    <div class="container py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <!-- Tarjeta principal con estilo glassmorphism -->
                <div class="pago-card">
                    <div class="pago-card-header">
                        <div class="header-icon">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div class="header-title">
                            <h4><i class="fas fa-coffee"></i> Pago en Efectivo</h4>
                            <p>Completa tu compra de forma rápida y segura</p>
                        </div>
                        <div class="coffee-decoration-header">
                            <span>☕</span>
                            <span>💰</span>
                            <span>☕</span>
                        </div>
                    </div>

                    <div class="pago-card-body">
                        <!-- Resumen del pedido -->
                        <div class="resumen-pedido">
                            <div class="resumen-header">
                                <i class="fas fa-receipt"></i>
                                <h5>Resumen del Pedido</h5>
                            </div>
                            
                            @php
                                $total = 0;
                                $itemsCount = 0;
                            @endphp
                            
                            <div class="productos-lista">
                                @foreach($carrito as $item)
                                    @php
                                        $subtotal = $item['precio'] * $item['cantidad'];
                                        $total += $subtotal;
                                        $itemsCount += $item['cantidad'];
                                    @endphp
                                    <div class="producto-item">
                                        <div class="producto-info">
                                            <span class="producto-nombre">{{ $item['nombre'] }}</span>
                                            <span class="producto-cantidad">x{{ $item['cantidad'] }}</span>
                                        </div>
                                        <span class="producto-subtotal">${{ number_format($subtotal, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="total-pagar">
                                <span>TOTAL A PAGAR:</span>
                                <span class="total-monto">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <!-- NUEVO: Selector de Mesas -->
                        <div class="mesas-container mb-4">
                            <div class="mesas-header">
                                <i class="fas fa-chair"></i>
                                <h5>Selecciona tu Mesa</h5>
                                <span class="mesas-subtitulo">Elige una mesa disponible</span>
                            </div>

                            <div class="plano-restaurant" id="mesasGrid">
                                <!-- Las mesas se generan dinámicamente con JavaScript -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando mesas...</span>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="mesa" id="mesaSeleccionada" value="" required>
                            <div class="mesa-error text-danger mt-2" style="display: none;">
                                <i class="fas fa-exclamation-circle"></i> Debes seleccionar una mesa
                            </div>
                        </div>

                        <!-- Formulario de pago -->
                        <form action="{{ route('carrito.procesar-pago') }}" method="POST" id="pagoForm">
                            @csrf
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-money-bill-wave"></i> Efectivo Recibido
                                </label>
                                <div class="input-efectivo-wrapper">
                                    <span class="moneda-simbolo">$</span>
                                    <input type="text" 
                                           class="input-efectivo" 
                                           id="efectivoRecibido" 
                                           name="efectivo_recibido" 
                                           required
                                           readonly
                                           value="0.00">
                                </div>
                                <div class="efectivo-indicador" id="efectivoIndicador">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Ingresa el efectivo usando la calculadora</span>
                                </div>
                            </div>

                            <!-- Campo oculto para la mesa -->
                            <input type="hidden" name="mesa" id="mesaInput" value="">

                            <!-- Calculadora moderna -->
                            <div class="calculadora-card">
                                <div class="calculadora-header">
                                    <i class="fas fa-calculator"></i>
                                    <h5>Calculadora de Pago</h5>
                                </div>
                                
                                <div class="calculadora-body">
                                    <!-- Billetes -->
                                    <div class="billetes-grid">
                                        @foreach([20, 50, 100, 200, 500, 1000] as $billete)
                                        <button type="button" class="billete-btn" data-value="{{ $billete }}">
                                            ${{ $billete }}
                                        </button>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Teclado numérico -->
                                    <div class="teclado-grid">
                                        @for($i = 1; $i <= 9; $i++)
                                        <button type="button" class="numero-btn" data-value="{{ $i }}">
                                            {{ $i }}
                                        </button>
                                        @endfor
                                        <button type="button" class="numero-btn decimal-btn" onclick="agregarDecimal()">
                                            .
                                        </button>
                                        <button type="button" class="numero-btn" data-value="0">
                                            0
                                        </button>
                                        <button type="button" class="limpiar-btn" onclick="limpiarCalculadora()">
                                            C
                                        </button>
                                    </div>
                                    
                                    <!-- Botones de acción rápida -->
                                    <div class="acciones-rapidas">
                                        <button type="button" class="accion-btn calcular" onclick="calcularVuelto()">
                                            <i class="fas fa-calculator"></i> Calcular Vuelto
                                        </button>
                                        <button type="button" class="accion-btn insertar" onclick="insertarTotal()">
                                            <i class="fas fa-dollar-sign"></i> Insertar Total
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="acciones-pago">
                                <button type="submit" class="btn-procesar" id="btnProcesar">
                                    <i class="fas fa-check-circle"></i> PROCESAR PAGO
                                    <span class="btn-overlay"></span>
                                </button>
                                <a href="{{ route('carrito.ver') }}" class="btn-volver">
                                    <i class="fas fa-arrow-left"></i> Volver al Carrito
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="pago-card-footer">
                        <div class="coffee-beans-footer">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación modificado para incluir mesa -->
<div class="modal fade" id="confirmacionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modern-modal">
            <div class="modal-header modal-header-warning">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="modal-title-wrapper">
                    <h5 class="modal-title">Confirmar Pedido</h5>
                    <p class="modal-subtitle">Verifica los detalles antes de continuar</p>
                </div>
                <button type="button" class="modal-close" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirmacion-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h4 class="confirmacion-title">¿Confirmar pedido?</h4>
                
                <div class="confirmacion-detalles">
                    <div class="detalle-row">
                        <span class="detalle-label">Mesa seleccionada:</span>
                        <span class="detalle-valor" id="modalMesa">-</span>
                    </div>
                    <div class="detalle-row">
                        <span class="detalle-label">Total a pagar:</span>
                        <span class="detalle-valor total">$<span id="modalTotal">{{ number_format($total, 2) }}</span></span>
                    </div>
                    <div class="detalle-row">
                        <span class="detalle-label">Efectivo recibido:</span>
                        <span class="detalle-valor efectivo">$<span id="modalEfectivo">0.00</span></span>
                    </div>
                    <div class="detalle-row destacado">
                        <span class="detalle-label">Cambio a entregar:</span>
                        <span class="detalle-valor cambio">$<span id="modalCambio">0.00</span></span>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> El mesero llevará tu orden a la mesa seleccionada
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn-confirmar" id="confirmarPagoBtn">
                    <i class="fas fa-check"></i> Confirmar Pedido
                    <span class="btn-overlay"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== ESTILOS GENERALES (mismos que las demás páginas) ===== */
    .pago-efectivo-container {
        position: relative;
        min-height: 100vh;
        background: linear-gradient(145deg, #faf0e6 0%, #f5e6d3 100%);
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        padding: 20px 0;
        overflow-x: hidden;
    }

    /* ===== ELEMENTOS DECORATIVOS - TAZAS BLANCAS ===== */
    .coffee-elements {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }

    .coffee-cup {
        position: absolute;
        opacity: 0.15;
        filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));
    }

    .cup-1 { top: 30px; left: 30px; transform: scale(0.7); }
    .cup-2 { bottom: 30px; right: 30px; transform: scale(0.7) rotate(-10deg); }
    .cup-3 { top: 50%; right: 40px; transform: scale(0.6) translateY(-50%); }

    .white-cup { background: linear-gradient(145deg, #ffffff, #f8f8f8) !important; }
    .white-handle { border-color: #f0f0f0 !important; border-right: 6px solid #ffffff !important; }

    .cup-top {
        width: 60px; height: 15px; border-radius: 50%;
        background: linear-gradient(145deg, #ffffff, #f0f0f0);
    }

    .cup-body {
        width: 50px; height: 45px; border-radius: 0 0 25px 25px;
        background: linear-gradient(145deg, #ffffff, #f5f5f5);
        top: -7px; position: relative;
    }

    .cup-handle {
        width: 18px; height: 30px; border: 5px solid #f0f0f0;
        border-left: none; border-radius: 0 15px 15px 0;
        position: absolute; right: -15px; top: 10px;
    }

    .steam {
        position: absolute; background: rgba(255, 255, 255, 0.5);
        border-radius: 50%; animation: steam 3s infinite;
    }

    .s1 { width: 10px; height: 10px; top: -15px; left: 15px; }
    .s2 { width: 8px; height: 8px; top: -20px; left: 25px; animation-delay: 0.5s; }
    .s3 { width: 6px; height: 6px; top: -18px; left: 35px; animation-delay: 1s; }

    @keyframes steam {
        0%, 100% { transform: translateY(0) scale(1); opacity: 0.5; }
        50% { transform: translateY(-10px) scale(1.2); opacity: 0.2; }
    }

    /* Granos de café */
    .coffee-bean {
        position: absolute; width: 15px; height: 7px; background: #8B4513;
        border-radius: 50%; opacity: 0.1; animation: float 20s infinite linear;
        transform: rotate(45deg);
    }

    .bean-1 { top: 15%; left: 5%; animation-delay: 0s; }
    .bean-2 { bottom: 20%; right: 5%; animation-delay: 5s; }
    .bean-3 { top: 40%; left: 8%; animation-delay: 8s; }
    .bean-4 { bottom: 30%; right: 8%; animation-delay: 12s; }
    .bean-5 { top: 70%; left: 3%; animation-delay: 15s; }

    @keyframes float {
        from { transform: translateY(0) rotate(45deg); opacity: 0.1; }
        to { transform: translateY(-100vh) rotate(405deg); opacity: 0; }
    }

    /* Partículas */
    .particle {
        position: absolute; width: 3px; height: 3px;
        background: rgba(139, 69, 19, 0.2); border-radius: 50%;
        animation: particle-float 15s infinite linear;
    }

    .particle-1 { top: 20%; left: 15%; animation-delay: 0s; }
    .particle-2 { top: 60%; right: 10%; animation-delay: 5s; }
    .particle-3 { top: 80%; left: 20%; animation-delay: 10s; }

    @keyframes particle-float {
        from { transform: translateY(0) scale(1); opacity: 0.3; }
        to { transform: translateY(-100vh) scale(0); opacity: 0; }
    }

    /* ===== TARJETA PRINCIPAL ===== */
    .pago-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 30px;
        box-shadow: 0 20px 40px rgba(139, 69, 19, 0.15);
        position: relative;
        z-index: 10;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
        animation: fadeInUp 0.8s ease;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header de la tarjeta */
    .pago-card-header {
        background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
        color: white;
        padding: 20px 25px;
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
        overflow: hidden;
    }

    @media (min-width: 768px) {
        .pago-card-header {
            padding: 25px 30px;
            gap: 20px;
        }
    }

    .pago-card-header::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2));
        transform: skewX(-20deg) translateX(100px);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        0% { transform: skewX(-20deg) translateX(100px); }
        20% { transform: skewX(-20deg) translateX(-200px); }
        100% { transform: skewX(-20deg) translateX(-200px); }
    }

    .header-icon {
        width: 50px; height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    @media (min-width: 768px) {
        .header-icon {
            width: 60px; height: 60px;
            border-radius: 20px;
            font-size: 2rem;
        }
    }

    .header-title h4 {
        margin: 0;
        font-weight: 700;
        font-size: 1.3rem;
    }

    @media (min-width: 768px) {
        .header-title h4 {
            font-size: 1.5rem;
        }
    }

    .header-title p {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 0.8rem;
    }

    @media (min-width: 768px) {
        .header-title p {
            font-size: 0.9rem;
        }
    }

    .coffee-decoration-header {
        margin-left: auto;
        font-size: 1.3rem;
    }

    @media (min-width: 768px) {
        .coffee-decoration-header {
            font-size: 1.5rem;
        }
    }

    .coffee-decoration-header span {
        margin: 0 3px;
        animation: bounce 2s infinite;
    }

    @media (min-width: 768px) {
        .coffee-decoration-header span {
            margin: 0 5px;
        }
    }

    .coffee-decoration-header span:nth-child(2) { animation-delay: 0.3s; }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    /* Cuerpo de la tarjeta */
    .pago-card-body {
        padding: 20px;
    }

    @media (min-width: 768px) {
        .pago-card-body {
            padding: 30px;
        }
    }

    /* Footer */
    .pago-card-footer {
        padding: 15px;
        text-align: center;
        border-top: 2px dashed #e8d5c0;
    }

    .coffee-beans-footer {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .coffee-beans-footer span {
        width: 10px; height: 15px;
        background: #6f4e3a;
        border-radius: 50%;
        transform: rotate(45deg);
        animation: bounce-footer 2s infinite;
        display: inline-block;
        opacity: 0.5;
    }

    @media (min-width: 768px) {
        .coffee-beans-footer span {
            width: 12px; height: 18px;
        }
    }

    .coffee-beans-footer span:nth-child(2) { animation-delay: 0.2s; }
    .coffee-beans-footer span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes bounce-footer {
        0%, 100% { transform: rotate(45deg) translateY(0); }
        50% { transform: rotate(45deg) translateY(-5px); }
    }

    /* ===== RESUMEN DEL PEDIDO ===== */
    .resumen-pedido {
        background: linear-gradient(145deg, #f8f4f0, #f0e8e0);
        border-radius: 20px;
        padding: 15px;
        margin-bottom: 25px;
    }

    @media (min-width: 768px) {
        .resumen-pedido {
            padding: 20px;
        }
    }

    .resumen-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e8d5c0;
    }

    .resumen-header i {
        color: #8B4513;
        font-size: 1.3rem;
    }

    .resumen-header h5 {
        margin: 0;
        color: #5D4037;
        font-weight: 700;
    }

    .productos-lista {
        max-height: 200px;
        overflow-y: auto;
        margin-bottom: 15px;
        padding-right: 5px;
    }

    @media (min-width: 768px) {
        .productos-lista {
            max-height: 250px;
        }
    }

    .producto-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #e8d5c0;
    }

    .producto-info {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .producto-nombre {
        font-weight: 600;
        color: #5D4037;
    }

    .producto-cantidad {
        color: #8B4513;
        font-size: 0.9rem;
        background: white;
        padding: 2px 8px;
        border-radius: 20px;
    }

    .producto-subtotal {
        font-weight: 600;
        color: #28a745;
    }

    .total-pagar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0 5px;
        border-top: 2px solid #8B4513;
        font-weight: 700;
        font-size: 1.2rem;
    }

    @media (min-width: 768px) {
        .total-pagar {
            font-size: 1.3rem;
        }
    }

    .total-monto {
        color: #28a745;
        font-size: 1.4rem;
    }

    @media (min-width: 768px) {
        .total-monto {
            font-size: 1.6rem;
        }
    }

    /* ===== SELECTOR DE MESAS ===== */
    .mesas-container {
        background: linear-gradient(145deg, #f8f4f0, #f0e8e0);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #e8d5c0;
    }

    .mesas-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e8d5c0;
        flex-wrap: wrap;
    }

    .mesas-header i {
        color: #8B4513;
        font-size: 1.5rem;
        background: white;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .mesas-header h5 {
        margin: 0;
        color: #5D4037;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .mesas-subtitulo {
        margin-left: auto;
        color: #8B4513;
        font-size: 0.9rem;
        background: rgba(139, 69, 19, 0.1);
        padding: 5px 12px;
        border-radius: 20px;
    }

    .mesas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 15px;
        margin-bottom: 10px;
    }

    @media (min-width: 768px) {
        .mesas-grid {
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
        }
    }

    .mesa-item {
        aspect-ratio: 1;
        background: white;
        border: 2px solid #e8d5c0;
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        padding: 10px;
    }

    .mesa-item:hover:not(.ocupada) {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #8B4513;
    }

    .mesa-item.seleccionada {
        background: linear-gradient(135deg, #8B4513, #A0522D);
        border-color: #8B4513;
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(139, 69, 19, 0.3);
    }

    .mesa-item.seleccionada .mesa-numero,
    .mesa-item.seleccionada .mesa-icono {
        color: white;
    }

    .mesa-item.ocupada {
        background: #f8f9fa;
        border-color: #dc3545;
        opacity: 0.6;
        cursor: not-allowed;
        filter: grayscale(0.5);
    }

    .mesa-item.ocupada .mesa-icono {
        color: #dc3545;
    }

    .mesa-item.ocupada .mesa-estado {
        color: #dc3545;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .mesa-icono {
        font-size: 2rem;
        color: #8B4513;
        margin-bottom: 5px;
    }

    .mesa-numero {
        font-weight: 700;
        font-size: 1.2rem;
        color: #5D4037;
    }

    .mesa-estado {
        font-size: 0.7rem;
        color: #28a745;
        font-weight: 600;
    }

    .mesa-item.seleccionada .mesa-estado {
        color: white;
    }

    .mesa-item::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.3s;
        pointer-events: none;
    }

    .mesa-item:hover::before:not(.ocupada) {
        opacity: 0.5;
    }

    @keyframes mesaSeleccionada {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .mesa-item.seleccionada .mesa-icono {
        animation: mesaSeleccionada 0.5s ease;
    }

    /* ===== FORMULARIO ===== */
    .form-group-modern {
        margin-bottom: 25px;
    }

    .form-label-modern {
        display: block;
        margin-bottom: 10px;
        color: #5D4037;
        font-weight: 600;
        font-size: 1rem;
    }

    .form-label-modern i {
        margin-right: 8px;
        color: #8B4513;
    }

    .input-efectivo-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .moneda-simbolo {
        position: absolute;
        left: 20px;
        font-size: 1.5rem;
        font-weight: 600;
        color: #8B4513;
        z-index: 2;
    }

    @media (min-width: 768px) {
        .moneda-simbolo {
            font-size: 2rem;
            left: 25px;
        }
    }

    .input-efectivo {
        width: 100%;
        padding: 15px 15px 15px 50px;
        border: 2px solid #e8d5c0;
        border-radius: 15px;
        font-size: 1.8rem;
        font-weight: 700;
        text-align: right;
        background: white;
        transition: all 0.3s ease;
    }

    @media (min-width: 768px) {
        .input-efectivo {
            padding: 20px 20px 20px 70px;
            font-size: 2.5rem;
        }
    }

    .input-efectivo.text-success {
        color: #28a745;
        border-color: #28a745;
    }

    .input-efectivo.text-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .efectivo-indicador {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .efectivo-indicador i {
        color: #8B4513;
    }

    /* ===== CALCULADORA ===== */
    .calculadora-card {
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 25px;
        border: 1px solid #e8d5c0;
    }

    .calculadora-header {
        background: linear-gradient(145deg, #f0e4d5, #e8d5c0);
        padding: 12px 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    @media (min-width: 768px) {
        .calculadora-header {
            padding: 15px 20px;
        }
    }

    .calculadora-header i {
        color: #8B4513;
        font-size: 1.3rem;
    }

    .calculadora-header h5 {
        margin: 0;
        color: #5D4037;
        font-weight: 600;
    }

    .calculadora-body {
        padding: 15px;
    }

    @media (min-width: 768px) {
        .calculadora-body {
            padding: 20px;
        }
    }

    /* Billetes */
    .billetes-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 15px;
    }

    @media (min-width: 768px) {
        .billetes-grid {
            gap: 10px;
            margin-bottom: 20px;
        }
    }

    .billete-btn {
        padding: 10px 5px;
        background: linear-gradient(135deg, #28a745, #218838);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    @media (min-width: 768px) {
        .billete-btn {
            padding: 12px;
            font-size: 1rem;
        }
    }

    .billete-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(40, 167, 69, 0.3);
    }

    /* Teclado */
    .teclado-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 15px;
    }

    @media (min-width: 768px) {
        .teclado-grid {
            gap: 10px;
            margin-bottom: 20px;
        }
    }

    .numero-btn {
        padding: 12px 5px;
        background: linear-gradient(145deg, #8B4513, #A0522D);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.2rem;
    }

    @media (min-width: 768px) {
        .numero-btn {
            padding: 15px;
            font-size: 1.3rem;
        }
    }

    .numero-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(139, 69, 19, 0.3);
    }

    .decimal-btn {
        background: linear-gradient(145deg, #17a2b8, #138496);
    }

    .limpiar-btn {
        padding: 12px 5px;
        background: linear-gradient(145deg, #dc3545, #c82333);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.2rem;
        grid-column: span 1;
    }

    @media (min-width: 768px) {
        .limpiar-btn {
            padding: 15px;
            font-size: 1.3rem;
        }
    }

    .limpiar-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(220, 53, 69, 0.3);
    }

    /* Acciones rápidas */
    .acciones-rapidas {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .accion-btn {
        padding: 12px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 0.9rem;
    }

    @media (min-width: 768px) {
        .accion-btn {
            padding: 15px;
            font-size: 1rem;
        }
    }

    .accion-btn.calcular {
        background: linear-gradient(145deg, #17a2b8, #138496);
        color: white;
    }

    .accion-btn.insertar {
        background: linear-gradient(145deg, #ffc107, #e0a800);
        color: #333;
    }

    .accion-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    /* ===== BOTONES DE ACCIÓN ===== */
    .acciones-pago {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    @media (min-width: 768px) {
        .acciones-pago {
            flex-direction: row;
            gap: 15px;
        }
    }

    .btn-procesar {
        flex: 2;
        padding: 15px;
        background: linear-gradient(135deg, #28a745, #218838);
        color: white;
        border: none;
        border-radius: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 1rem;
        position: relative;
        overflow: hidden;
    }

    @media (min-width: 768px) {
        .btn-procesar {
            padding: 18px;
            font-size: 1.2rem;
        }
    }

    .btn-volver {
        flex: 1;
        padding: 15px;
        background: linear-gradient(145deg, #6c757d, #5a6268);
        color: white;
        text-decoration: none;
        border-radius: 15px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 1rem;
        text-align: center;
    }

    @media (min-width: 768px) {
        .btn-volver {
            padding: 18px;
            font-size: 1.1rem;
        }
    }

    .btn-procesar:hover, .btn-volver:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        color: white;
    }

    .btn-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.5s, height 0.5s;
    }

    .btn-procesar:hover .btn-overlay {
        width: 300px;
        height: 300px;
    }

    /* ===== MODAL MODERNO ===== */
    .modern-modal {
        border: none;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(0,0,0,0.3);
    }

    .modal-header-warning {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: white;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .modal-icon {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .modal-title-wrapper {
        flex: 1;
    }

    .modal-title {
        margin: 0;
        font-weight: 700;
    }

    .modal-subtitle {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 0.85rem;
    }

    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .modal-close:hover {
        background: rgba(255,255,255,0.3);
        transform: rotate(90deg);
    }

    .confirmacion-icon {
        text-align: center;
        margin-bottom: 15px;
    }

    .confirmacion-icon i {
        font-size: 3.5rem;
        color: #28a745;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .confirmacion-title {
        text-align: center;
        color: #5D4037;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .confirmacion-detalles {
        background: linear-gradient(145deg, #f8f4f0, #f0e8e0);
        border-radius: 15px;
        padding: 20px;
    }

    .detalle-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e8d5c0;
    }

    .detalle-row:last-child {
        border-bottom: none;
    }

    .detalle-row.destacado {
        background: white;
        margin: 5px -5px 0;
        padding: 15px 10px;
        border-radius: 12px;
        font-weight: 700;
    }

    .detalle-label {
        color: #5D4037;
    }

    .detalle-valor {
        font-weight: 600;
    }

    .detalle-valor.total {
        color: #28a745;
    }

    .detalle-valor.efectivo {
        color: #17a2b8;
    }

    .detalle-valor.cambio {
        color: #ffc107;
    }

    .modal-footer {
        padding: 20px;
        border-top: 2px solid #eee;
        display: flex;
        gap: 10px;
    }

    .btn-cancelar {
        flex: 1;
        padding: 12px;
        background: linear-gradient(145deg, #6c757d, #5a6268);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .btn-confirmar {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #28a745, #218838);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        position: relative;
        overflow: hidden;
    }

    .btn-cancelar:hover, .btn-confirmar:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .btn-confirmar:hover .btn-overlay {
        width: 300px;
        height: 300px;
    }

    /* ===== PLANO RESTAURANTE PERSONALIZADO ===== */

.plano-restaurant {
    display: grid;
    grid-template-columns: 1fr 2fr;
    grid-template-rows: auto auto;
    gap: 30px;
    min-height: 400px;
    position: relative;
}

/* Columna izquierda (4 mesas cuadradas) */
.col-izquierda {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Zona central (mesas redondas) */
.zona-central {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    align-content: center;
}

/* Barra inferior */
.barra-inferior {
    grid-column: 1 / span 2;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    border-top: 2px dashed #d7c2aa;
    padding-top: 15px;
}

.barra-label {
    font-weight: 700;
    color: #8B4513;
}

/* TIPOS DE MESAS */
.mesa-item {
    background: white;
    border: 2px solid #e8d5c0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: 0.3s;
    padding: 10px;
}

/* Cuadradas */
.mesa-item.cuadrada {
    width: 90px;
    height: 90px;
    border-radius: 15px;
}

/* Redondas */
.mesa-item.redonda {
    width: 100px;
    height: 100px;
    border-radius: 50%;
}

/* Barra */
.mesa-item.barra {
    width: 120px;
    height: 50px;
    border-radius: 25px;
}

/* Hover */
.mesa-item:hover {
    transform: scale(1.05);
    border-color: #8B4513;
}

/* Seleccionada */
.mesa-item.seleccionada {
    background: linear-gradient(135deg, #8B4513, #A0522D);
    color: white;
    border-color: #8B4513;
}

.mesa-item.seleccionada .mesa-estado {
    color: white;
}
</style>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<script>
let efectivoActual = 0;
let mesaSeleccionada = null;
const totalPagar = {{ $total }};

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    cargarMesas();
    
    // Botones numéricos
    document.querySelectorAll('.numero-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            agregarNumero(this.dataset.value);
        });
    });
    
    // Botones de billetes
    document.querySelectorAll('.billete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            agregarBillete(parseFloat(this.dataset.value));
        });
    });
    
    // Confirmar pago
    document.getElementById('confirmarPagoBtn').addEventListener('click', function() {
        document.getElementById('pagoForm').submit();
    });
    
    // Mostrar confirmación al enviar formulario
    document.getElementById('pagoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const mesaError = document.querySelector('.mesa-error');
        
        if (!mesaSeleccionada) {
            mesaError.style.display = 'block';
            return;
        }
        
        if (efectivoActual >= totalPagar) {
            actualizarModal();
            new bootstrap.Modal(document.getElementById('confirmacionModal')).show();
        } else {
            alert('El efectivo recibido es insuficiente');
        }
    });

    // Ocultar error de mesa cuando se selecciona una
    document.addEventListener('mesaSeleccionada', function() {
        document.querySelector('.mesa-error').style.display = 'none';
    });
});

function cargarMesas() {
    const mesasGrid = document.getElementById('mesasGrid');

    mesasGrid.innerHTML = `
        <!-- Columna izquierda (mesas cuadradas) -->
        <div class="col-izquierda">
            ${crearMesaHTML(1, 'cuadrada')}
            ${crearMesaHTML(2, 'cuadrada')}
            ${crearMesaHTML(3, 'cuadrada')}
            ${crearMesaHTML(4, 'cuadrada')}
        </div>

        <!-- Zona central (mesas redondas) -->
        <div class="zona-central">
            ${crearMesaHTML(5, 'redonda')}
            ${crearMesaHTML(6, 'redonda')}
            ${crearMesaHTML(7, 'redonda')}
            ${crearMesaHTML(8, 'redonda')}
            ${crearMesaHTML(9, 'redonda')}
        </div>

        <!-- Barra inferior -->
        <div class="barra-inferior">
            <div class="barra-label">Barra</div>
            ${crearMesaHTML(10, 'barra')}
        </div>
    `;
}

function crearMesaHTML(numero, tipo) {
    return `
        <div class="mesa-item ${tipo}" data-mesa="${numero}" onclick="seleccionarMesa(${numero})">
            <span class="mesa-numero">Mesa ${numero}</span>
            <span class="mesa-estado">Disponible</span>
        </div>
    `;
}

function seleccionarMesa(numero) {
    // Quitar selección anterior
    document.querySelectorAll('.mesa-item').forEach(item => {
        item.classList.remove('seleccionada');
    });
    
    // Seleccionar nueva mesa
    const mesaElement = document.querySelector(`.mesa-item[data-mesa="${numero}"]`);
    mesaElement.classList.add('seleccionada');
    
    mesaSeleccionada = numero;
    document.getElementById('mesaInput').value = numero;
    
    // Disparar evento personalizado
    const event = new CustomEvent('mesaSeleccionada');
    document.dispatchEvent(event);
}

function agregarNumero(numero) {
    let valor = document.getElementById('efectivoRecibido').value;
    if (valor === '0.00' || valor === '0') {
        valor = numero;
    } else {
        if (valor.includes('.')) {
            const partes = valor.split('.');
            if (partes[1].length < 2) {
                valor = valor + numero;
            }
        } else {
            valor = valor + numero;
        }
    }
    actualizarEfectivo(valor);
}

function agregarBillete(monto) {
    const actual = parseFloat(document.getElementById('efectivoRecibido').value) || 0;
    const nuevo = actual + monto;
    actualizarEfectivo(nuevo.toFixed(2));
}

function agregarDecimal() {
    let valor = document.getElementById('efectivoRecibido').value;
    if (!valor.includes('.')) {
        valor = valor + '.';
        actualizarEfectivo(valor);
    }
}

function limpiarCalculadora() {
    actualizarEfectivo('0.00');
}

function insertarTotal() {
    actualizarEfectivo(totalPagar.toFixed(2));
}

function calcularVuelto() {
    const vuelto = efectivoActual - totalPagar;
    if (vuelto > 0) {
        alert(`Cambio a entregar: $${vuelto.toFixed(2)}`);
    } else {
        alert('Efectivo insuficiente');
    }
}

function actualizarEfectivo(valor) {
    valor = valor.replace(/[^0-9.]/g, '');
    
    let numero = parseFloat(valor);
    if (isNaN(numero)) numero = 0;
    
    const valorFormateado = numero.toFixed(2);
    document.getElementById('efectivoRecibido').value = valorFormateado;
    efectivoActual = numero;
    
    const input = document.getElementById('efectivoRecibido');
    if (numero >= totalPagar) {
        input.classList.remove('text-danger');
        input.classList.add('text-success');
    } else {
        input.classList.remove('text-success');
        input.classList.add('text-danger');
    }
}

function actualizarModal() {
    const efectivo = efectivoActual;
    const cambio = efectivo - totalPagar;
    
    document.getElementById('modalMesa').textContent = `Mesa ${mesaSeleccionada}`;
    document.getElementById('modalEfectivo').textContent = efectivo.toFixed(2);
    document.getElementById('modalCambio').textContent = cambio.toFixed(2);
}
</script>
@endsection