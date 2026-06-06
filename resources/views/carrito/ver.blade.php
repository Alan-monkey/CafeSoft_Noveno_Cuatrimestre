@extends('layouts.app')
@section('content')

<div class="carrito-container">
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

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <!-- Tarjeta principal -->
                <div class="carrito-card">
                    <div class="carrito-header">
                        <div class="header-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="header-title">
                            <h4><i class="fas fa-coffee"></i> Carrito de Compras</h4>
                            <p>Revisa tus productos antes de pagar</p>
                        </div>
                        <div class="coffee-decoration-header">
                            <span>🛒</span>
                            <span>☕</span>
                            <span>💰</span>
                        </div>
                    </div>

                    <div class="carrito-body">
                        <!-- Alertas -->
                        @if(session('success'))
                            <div class="alert-modern alert-success-modern mb-4">
                                <i class="fas fa-check-circle"></i>
                                <div class="alert-content">
                                    {{ session('success') }}
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert-modern alert-error-modern mb-4">
                                <i class="fas fa-exclamation-circle"></i>
                                <div class="alert-content">
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        @if(empty($carrito))
                            <!-- ===== CARRITO VACÍO ===== -->
                            <div class="empty-cart">
                                <div class="empty-cart-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h3>Tu carrito está vacío</h3>
                                <p>Agrega algunos productos deliciosos para continuar</p>
                                <a href="/productos/leer" class="btn-ver-productos">
                                    <i class="fas fa-coffee"></i> Ver Productos
                                </a>
                            </div>

                            <!-- ===== PRODUCTOS SUGERIDOS (CUANDO CARRITO VACÍO) ===== -->
                            <div class="productos-sugeridos mt-5">
                                <div class="seccion-header">
                                    <div class="header-decoration">
                                        <i class="fas fa-mug-hot"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-mug-hot"></i>
                                    </div>
                                    <h3>Productos disponibles</h3>
                                    <p>Comienza a llenar tu carrito con nuestras delicias</p>
                                </div>

                                <div class="productos-grid">
                                    @foreach($productos as $producto)
                                        @php
                                            $stock = $producto->stock ?? 0;
                                        @endphp

                                        
                                            <div class="producto-card-sugerido">

                                                <div class="producto-imagen-sugerido">
                                                    @if($producto->imagen)
                                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                                                    @else
                                                        <div class="sugerido-placeholder">
                                                            <i class="fas fa-coffee"></i>
                                                        </div>
                                                    @endif

                                                </div>
                                                <div class="producto-info-sugerido">
                                                    <h5>{{ $producto->nombre }}</h5>
                                                    <p class="sugerido-descripcion">{{ Str::limit($producto->descripcion, 50) }}</p>
                                                    <div class="sugerido-footer">
                                                        <span class="sugerido-precio">${{ number_format($producto->precio, 2) }}</span>
                                                        <form action="{{ route('carrito.agregar', $producto->_id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn-agregar-sugerido" title="Agregar al carrito">
                                                                <i class="fas fa-cart-plus"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- ===== TABLA DE PRODUCTOS EN CARRITO ===== -->
                            <div class="table-responsive">
                                <table class="table carrito-table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $total = 0; @endphp
                                        @foreach($carrito as $id => $item)
                                            @php 
                                                $subtotal = $item['precio'] * $item['cantidad'];
                                                $total += $subtotal;
                                            @endphp
                                            <tr class="producto-row">
                                                <td data-label="Producto">
                                                    <div class="producto-info">
                                                        @if(!empty($item['imagen']))
                                                            <img src="{{ asset('storage/' . $item['imagen']) }}" 
                                                                 alt="{{ $item['nombre'] }}" 
                                                                 class="producto-img">
                                                        @else
                                                            <div class="producto-img-placeholder">
                                                                <i class="fas fa-coffee"></i>
                                                            </div>
                                                        @endif
                                                        <div class="producto-detalles">
                                                            <span class="producto-nombre">{{ $item['nombre'] }}</span>
                                                            @if(isset($item['stock_disponible']))
                                                                <small class="stock-info">
                                                                    <i class="fas fa-boxes"></i> 
                                                                    Disponible: {{ $item['stock_disponible'] }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="Precio">
                                                    <span class="precio-cell">${{ number_format($item['precio'], 2) }}</span>
                                                </td>
                                                <td data-label="Cantidad">
                                                    <form action="{{ route('carrito.actualizar', $id) }}" method="POST" class="cantidad-form">
                                                        @csrf
                                                        <div class="cantidad-control">
                                                            <button type="button" class="btn-cantidad disminuir" onclick="disminuirCantidad(this)">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                            <input type="number" name="cantidad" value="{{ $item['cantidad'] }}" 
                                                                   min="1" max="99" class="input-cantidad" readonly>
                                                            <button type="button" class="btn-cantidad aumentar" onclick="aumentarCantidad(this)">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                        <button type="submit" class="btn-actualizar" style="display: none;">Actualizar</button>
                                                    </form>
                                                </td>
                                                <td data-label="Subtotal">
                                                    <span class="subtotal-cell">${{ number_format($subtotal, 2) }}</span>
                                                </td>
                                                <td data-label="Acciones">
                                                    <div class="acciones-cell">
                                                        <a href="{{ route('carrito.eliminar', $id) }}" 
                                                           class="btn-eliminar"
                                                           onclick="return confirm('¿Eliminar este producto del carrito?')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row">
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td colspan="2">
                                                <span class="total-cell">${{ number_format($total, 2) }}</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- ===== ACCIONES DEL CARRITO ===== -->
                            <div class="carrito-actions">
                                <div class="actions-left">
                                    <a href="{{ route('carrito.vaciar') }}" class="btn-vaciar" 
                                       onclick="return confirm('¿Vaciar todo el carrito?')">
                                        <i class="fas fa-trash-alt"></i> Vaciar Carrito
                                    </a>
                                </div>
                                <div class="actions-right">
                                    <a href="/productos/leer" class="btn-seguir">
                                        <i class="fas fa-plus-circle"></i> Seguir Comprando
                                    </a>
                                    <a href="{{ route('carrito.mostrar-pago') }}" class="btn-pagar">
                                        <i class="fas fa-credit-card"></i> Proceder al Pago
                                    </a>
                                </div>
                            </div>

                            <!-- ===== RESUMEN DEL CARRITO ===== -->
                            <div class="carrito-resumen">
                                <div class="resumen-item">
                                    <span>Subtotal:</span>
                                    <span>${{ number_format($total, 2) }}</span>
                                </div>
                                <div class="resumen-item">
                                    <span>Envío:</span>
                                    <span class="text-success">Gratis</span>
                                </div>
                                <div class="resumen-item total">
                                    <span>Total a pagar:</span>
                                    <span>${{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            <!-- ===== PRODUCTOS DESTACADOS PARA AGREGAR MÁS ===== -->
                            <div class="productos-destacados mt-5">
                                <div class="seccion-header">
                                    <div class="header-decoration">
                                        <i class="fas fa-coffee"></i>
                                        <i class="fas fa-plus-circle"></i>
                                        <i class="fas fa-coffee"></i>
                                    </div>
                                    <h3>¿Quieres agregar más productos?</h3>
                                    <p>Selecciona de nuestros productos destacados</p>
                                </div>

                                <div class="productos-grid-mini">
                                    @foreach($productos as $producto)
                                        @php
                                            $enCarrito = isset($carrito[$producto->_id]);
                                            $stock = $producto->stock ?? 0;
                                        @endphp

                                        
                                            @if(!$enCarrito)
                                                <div class="producto-mini-card">

                                                <div class="producto-mini-imagen">
                                                    @if($producto->imagen)
                                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                                                    @else
                                                        <div class="mini-placeholder">
                                                            <i class="fas fa-coffee"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="producto-mini-info">
                                                    <h5>{{ $producto->nombre }}</h5>
                                                    <span class="mini-precio">${{ number_format($producto->precio, 2) }}</span>
                                                    <small class="mini-stock">
                                                        <i class="fas fa-boxes"></i> {{ $stock }} disponibles
                                                    </small>
                                                    <form action="{{ route('carrito.agregar', $producto->_id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn-agregar-mini">
                                                            <i class="fas fa-cart-plus"></i> Agregar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="ver-mas-container text-center mt-4">
                                    <a href="/productos/leer" class="btn-ver-mas">
                                        <i class="fas fa-eye"></i> Ver todos los productos
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Footer con decoración -->
                    <div class="carrito-footer">
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

<!-- Script para manejar cantidades -->
<script>
function disminuirCantidad(btn) {
    const form = btn.closest('.cantidad-form');
    const input = form.querySelector('.input-cantidad');
    let valor = parseInt(input.value);
    if (valor > 1) {
        input.value = valor - 1;
        form.submit();
    }
}

function aumentarCantidad(btn) {
    const form = btn.closest('.cantidad-form');
    const input = form.querySelector('.input-cantidad');
    let valor = parseInt(input.value);
    if (valor < 99) {
        input.value = valor + 1;
        form.submit();
    }
}

// Auto-submit cuando cambia la cantidad manualmente
document.querySelectorAll('.input-cantidad').forEach(input => {
    input.addEventListener('change', function() {
        if (this.value >= 1 && this.value <= 99) {
            this.closest('form').submit();
        }
    });
});
</script>

<style>
    /* ===== ESTILOS GENERALES ===== */
    .carrito-container {
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

    .cup-top { width: 60px; height: 15px; border-radius: 50%; background: linear-gradient(145deg, #ffffff, #f0f0f0); }
    .cup-body { width: 50px; height: 45px; border-radius: 0 0 25px 25px; background: linear-gradient(145deg, #ffffff, #f5f5f5); top: -7px; position: relative; }
    .cup-handle { width: 18px; height: 30px; border: 5px solid #f0f0f0; border-left: none; border-radius: 0 15px 15px 0; position: absolute; right: -15px; top: 10px; }

    .steam {
        position: absolute;
        background: rgba(255,255,255,0.5);
        border-radius: 50%;
        animation: steam 3s infinite;
    }

    .s1 { width: 10px; height: 10px; top: -15px; left: 15px; }
    .s2 { width: 8px; height: 8px; top: -20px; left: 25px; animation-delay: 0.5s; }
    .s3 { width: 6px; height: 6px; top: -18px; left: 35px; animation-delay: 1s; }

    @keyframes steam {
        0%,100% { transform: translateY(0) scale(1); opacity: 0.5; }
        50% { transform: translateY(-10px) scale(1.2); opacity: 0.2; }
    }

    .coffee-bean {
        position: absolute;
        width: 15px;
        height: 7px;
        background: #8B4513;
        border-radius: 50%;
        opacity: 0.1;
        animation: float 20s infinite linear;
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

    .particle {
        position: absolute;
        width: 3px;
        height: 3px;
        background: rgba(139,69,19,0.2);
        border-radius: 50%;
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
    .carrito-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 30px;
        box-shadow: 0 20px 40px rgba(139, 69, 19, 0.15);
        position: relative;
        z-index: 10;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.3);
        animation: fadeInUp 0.8s ease;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== HEADER ===== */
    .carrito-header {
        background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
        color: white;
        padding: 25px 30px;
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        overflow: hidden;
    }

    .carrito-header::after {
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
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .header-title h4 {
        margin: 0;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .header-title p {
        margin: 5px 0 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .coffee-decoration-header {
        margin-left: auto;
        font-size: 1.5rem;
    }

    .coffee-decoration-header span {
        margin: 0 5px;
        animation: bounce 2s infinite;
        display: inline-block;
    }

    @keyframes bounce {
        0%,100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    /* ===== CUERPO ===== */
    .carrito-body {
        padding: 35px;
    }

    /* ===== TABLA ===== */
    .carrito-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    .carrito-table thead th {
        background: #f8f4f0;
        color: #5D4037;
        font-weight: 600;
        padding: 16px;
        border: none;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 16px;
    }

    .carrito-table thead th i {
        margin-right: 8px;
        color: #8B4513;
    }

    .carrito-table tbody tr {
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }

    .carrito-table tbody tr:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 30px rgba(139,69,19,0.15);
    }

    .carrito-table tbody td {
        padding: 20px 16px;
        border: none;
        vertical-align: middle;
    }

    /* Producto info */
    .producto-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .producto-img {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        object-fit: cover;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: 3px solid white;
    }

    .producto-img-placeholder {
        width: 70px;
        height: 70px;
        background: linear-gradient(145deg, #f0e4d5, #e8d5c0);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8B4513;
        font-size: 2rem;
    }

    .producto-detalles {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .producto-nombre {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .stock-info {
        color: #27ae60;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Precios */
    .precio-cell {
        font-weight: 600;
        color: #8B4513;
        font-size: 1.1rem;
    }

    .subtotal-cell {
        font-weight: 700;
        color: #27ae60;
        font-size: 1.1rem;
    }

    .total-cell {
        font-size: 1.3rem;
        font-weight: 700;
        color: #8B4513;
        background: linear-gradient(145deg, #fff3e0, #ffe9d4);
        padding: 8px 20px;
        border-radius: 50px;
        display: inline-block;
    }

    /* Control de cantidad */
    .cantidad-control {
        display: flex;
        align-items: center;
        gap: 5px;
        background: #f8f4f0;
        border-radius: 30px;
        padding: 3px;
        width: fit-content;
    }

    .btn-cantidad {
        width: 35px;
        height: 35px;
        border: none;
        background: white;
        color: #8B4513;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-cantidad:hover {
        background: #8B4513;
        color: white;
        transform: scale(1.1);
    }

    .input-cantidad {
        width: 50px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        color: #2c3e50;
        font-size: 1rem;
    }

    .input-cantidad:focus {
        outline: none;
    }

    /* Botón eliminar */
    .btn-eliminar {
        color: #e74c3c;
        font-size: 1.3rem;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-eliminar:hover {
        color: #c0392b;
        transform: scale(1.2);
    }

    /* ===== ACCIONES DEL CARRITO ===== */
    .carrito-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px dashed #e8d5c0;
    }

    .actions-left, .actions-right {
        display: flex;
        gap: 15px;
    }

    .btn-vaciar {
        background: #fee;
        color: #e74c3c;
        padding: 12px 25px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid #e74c3c;
    }

    .btn-vaciar:hover {
        background: #e74c3c;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(231,76,60,0.3);
    }

    .btn-seguir {
        background: #f0e4d5;
        color: #8B4513;
        padding: 12px 25px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-seguir:hover {
        background: #e8d5c0;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(139,69,19,0.2);
    }

    .btn-pagar {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 8px 20px rgba(46,204,113,0.3);
    }

    .btn-pagar:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(46,204,113,0.4);
    }

    /* ===== RESUMEN ===== */
    .carrito-resumen {
        background: #f8f4f0;
        border-radius: 20px;
        padding: 20px;
        margin-top: 30px;
        max-width: 400px;
        margin-left: auto;
    }

    .resumen-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        color: #5D4037;
    }

    .resumen-item.total {
        border-top: 2px solid #d9b382;
        margin-top: 10px;
        padding-top: 15px;
        font-size: 1.2rem;
        font-weight: 700;
        color: #8B4513;
    }

    .text-success {
        color: #27ae60;
        font-weight: 600;
    }

    /* ===== CARRITO VACÍO ===== */
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-cart-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(145deg, #f0e4d5, #e8d5c0);
        border-radius: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        font-size: 3.5rem;
        color: #8B4513;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%,100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    .empty-cart h3 {
        color: #8B4513;
        font-size: 1.8rem;
        margin-bottom: 10px;
    }

    .empty-cart p {
        color: #8B6B4F;
        font-size: 1.1rem;
        margin-bottom: 25px;
    }

    .btn-ver-productos {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #8B4513, #A0522D);
        color: white;
        padding: 15px 40px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(139,69,19,0.3);
    }

    .btn-ver-productos:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(139,69,19,0.4);
        color: white;
    }

    /* ===== SECCIÓN DE PRODUCTOS ===== */
    .seccion-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .header-decoration {
        font-size: 2rem;
        color: #8B4513;
        margin-bottom: 10px;
    }

    .header-decoration i {
        margin: 0 10px;
        animation: bounce 2s infinite;
    }

    .header-decoration i:nth-child(2) {
        animation-delay: 0.3s;
        color: #27ae60;
    }

    .seccion-header h3 {
        color: #8B4513;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .seccion-header p {
        color: #8B6B4F;
    }

    /* Grid de productos mini */
    .productos-grid-mini {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .producto-mini-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 1px solid rgba(139,69,19,0.1);
    }

    .producto-mini-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(139,69,19,0.15);
    }

    .producto-mini-imagen {
        height: 120px;
        overflow: hidden;
    }

    .producto-mini-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .mini-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(145deg, #f0e4d5, #e8d5c0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #8B4513;
    }

    .producto-mini-info {
        padding: 15px;
    }

    .producto-mini-info h5 {
        margin: 0 0 8px 0;
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 600;
    }

    .mini-precio {
        display: block;
        font-weight: 700;
        color: #27ae60;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .mini-stock {
        display: block;
        color: #7f8c8d;
        font-size: 0.8rem;
        margin-bottom: 10px;
    }

    .btn-agregar-mini {
        width: 100%;
        background: linear-gradient(135deg, #8B4513, #A0522D);
        color: white;
        border: none;
        padding: 8px;
        border-radius: 10px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .btn-agregar-mini:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(139,69,19,0.3);
    }

    /* Grid de productos sugeridos */
    .productos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .producto-card-sugerido {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .producto-card-sugerido:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(139,69,19,0.15);
    }

    .producto-imagen-sugerido {
        height: 150px;
        overflow: hidden;
        position: relative;
    }

    .producto-imagen-sugerido img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sugerido-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(145deg, #f0e4d5, #e8d5c0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #8B4513;
    }

    .badge-stock-bajo {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #e74c3c;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        animation: pulse 2s infinite;
    }

    .producto-info-sugerido {
        padding: 15px;
    }

    .producto-info-sugerido h5 {
        margin: 0 0 8px 0;
        color: #2c3e50;
        font-weight: 600;
    }

    .sugerido-descripcion {
        color: #7f8c8d;
        font-size: 0.85rem;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .sugerido-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sugerido-precio {
        font-weight: 700;
        color: #27ae60;
        font-size: 1.2rem;
    }

    .btn-agregar-sugerido {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #8B4513, #A0522D);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-agregar-sugerido:hover {
        transform: scale(1.1);
        box-shadow: 0 5px 15px rgba(139,69,19,0.3);
    }

    .btn-ver-mas {
        display: inline-block;
        background: #f0e4d5;
        color: #8B4513;
        padding: 12px 30px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-ver-mas:hover {
        background: #8B4513;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(139,69,19,0.2);
    }

    /* ===== ALERTAS ===== */
    .alert-modern {
        padding: 18px 20px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.5s ease;
    }

    .alert-success-modern {
        background: linear-gradient(145deg, #d4edda, #c3e6cb);
        border-left: 6px solid #28a745;
        color: #155724;
    }

    .alert-error-modern {
        background: linear-gradient(145deg, #f8d7da, #f5c6cb);
        border-left: 6px solid #dc3545;
        color: #721c24;
    }

    .alert-modern i {
        font-size: 1.5rem;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== FOOTER ===== */
    .carrito-footer {
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
        width: 12px;
        height: 18px;
        background: #8B4513;
        border-radius: 50%;
        transform: rotate(45deg);
        animation: bounce-footer 2s infinite;
        display: inline-block;
        opacity: 0.5;
    }

    @keyframes bounce-footer {
        0%,100% { transform: rotate(45deg) translateY(0); }
        50% { transform: rotate(45deg) translateY(-5px); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .coffee-cup {
            display: none;
        }

        .carrito-header {
            flex-direction: column;
            text-align: center;
            padding: 20px;
        }

        .coffee-decoration-header {
            margin: 0 auto;
        }

        .carrito-body {
            padding: 20px;
        }

        .carrito-table thead {
            display: none;
        }

        .carrito-table tbody tr {
            display: block;
            margin-bottom: 20px;
        }

        .carrito-table tbody td {
            display: block;
            text-align: right;
            padding: 12px 15px;
            position: relative;
            border-bottom: 1px solid #eee;
        }

        .carrito-table tbody td:before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            font-weight: 600;
            color: #8B4513;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .producto-info {
            justify-content: flex-end;
        }

        .cantidad-control {
            margin-left: auto;
        }

        .carrito-actions {
            flex-direction: column;
            gap: 15px;
        }

        .actions-left, .actions-right {
            width: 100%;
            justify-content: center;
        }

        .carrito-resumen {
            margin: 20px auto 0;
        }

        .productos-grid-mini,
        .productos-grid {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .producto-mini-imagen {
            height: 100px;
        }
    }
</style>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

@endsection