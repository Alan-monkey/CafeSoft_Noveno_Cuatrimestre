<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CaféSoft | Análisis de Combos - Market Basket</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome y Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(145deg, #faf0e6 0%, #f3e5d8 100%);
            color: #3E2723;
            padding: 2rem;
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== ELEMENTOS DECORATIVOS CAFETEROS ===== */
        .coffee-decor {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .coffee-cup {
            position: absolute;
            opacity: 0.1;
            transition: all 0.2s;
        }
        .cup-1 { top: 20px; left: 20px; transform: scale(0.9); }
        .cup-2 { bottom: 30px; right: 30px; transform: scale(0.8) rotate(8deg); }
        .cup-3 { top: 50%; right: 60px; transform: scale(0.6) rotate(-5deg); }

        .cup-top {
            width: 70px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .cup-body {
            width: 60px;
            height: 55px;
            background: #fff;
            border-radius: 0 0 30px 30px;
            margin-top: -5px;
            position: relative;
        }
        .cup-handle {
            width: 22px;
            height: 38px;
            border: 5px solid #fff;
            border-left: none;
            border-radius: 0 20px 20px 0;
            position: absolute;
            right: -18px;
            top: 10px;
        }
        .steam {
            position: absolute;
            background: rgba(255,255,240,0.6);
            border-radius: 50%;
            animation: floatSteam 3s infinite ease-in-out;
        }
        .s1 { width: 10px; height: 10px; top: -15px; left: 15px; }
        .s2 { width: 8px; height: 8px; top: -22px; left: 28px; animation-delay: 0.5s; }
        .s3 { width: 6px; height: 6px; top: -18px; left: 40px; animation-delay: 1s; }

        @keyframes floatSteam {
            0% { transform: translateY(0) scale(0.8); opacity: 0.6; }
            100% { transform: translateY(-20px) scale(1.3); opacity: 0; }
        }

        .coffee-bean {
            position: absolute;
            width: 22px;
            height: 11px;
            background: #6f4e2e;
            border-radius: 50%;
            opacity: 0.08;
            transform: rotate(35deg);
            animation: slowDrift 25s infinite linear;
        }
        .bean-1 { top: 12%; left: 3%; animation-delay: 0s; }
        .bean-2 { bottom: 18%; right: 6%; animation-delay: 5s; }
        .bean-3 { top: 55%; left: 85%; animation-delay: 10s; }
        .bean-4 { top: 70%; left: 10%; animation-delay: 15s; }
        .bean-5 { bottom: 40%; right: 15%; animation-delay: 20s; }

        @keyframes slowDrift {
            from { transform: translateY(0) rotate(35deg); opacity: 0.08; }
            to { transform: translateY(-150vh) rotate(395deg); opacity: 0; }
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(139,69,19,0.15);
            border-radius: 50%;
            animation: particleFloat 20s infinite linear;
        }
        .particle-1 { top: 20%; left: 10%; animation-delay: 0s; }
        .particle-2 { top: 60%; right: 15%; animation-delay: 7s; }
        .particle-3 { top: 80%; left: 25%; animation-delay: 12s; }

        @keyframes particleFloat {
            0% { transform: translateY(0) scale(1); opacity: 0.3; }
            100% { transform: translateY(-100vh) scale(0); opacity: 0; }
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        /* ===== HEADER ESTILO CAFETERÍA ===== */
        .hero-header {
            background: linear-gradient(135deg, #5D3A1A, #8B5A2B);
            border-radius: 2rem 2rem 2rem 2rem;
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.25);
        }

        .hero-header::after {
            content: "🛒";
            font-size: 180px;
            position: absolute;
            right: -20px;
            bottom: -50px;
            opacity: 0.08;
            pointer-events: none;
            transform: rotate(-10deg);
        }

        .hero-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .hero-header h1 i {
            font-size: 2rem;
            color: #FFD966;
        }

        .hero-header p {
            color: rgba(255,245,220,0.9);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ===== TARJETAS DE ESTADÍSTICAS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1.2rem;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,.08);
            border-left: 6px solid #8B4513;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(139,69,19,.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(145deg, #faf0e6, #f5e6d3);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #8B4513;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content .label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8B6B4F;
            font-weight: 600;
        }

        .stat-content .value {
            font-size: 2rem;
            font-weight: 800;
            color: #3E2723;
            line-height: 1;
            margin-top: 5px;
        }

        /* ===== TARJETA PRINCIPAL ===== */
        .card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            padding: 1.8rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 15px 35px rgba(80, 45, 20, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(139,69,19,.1);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(139,69,19,.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid #F2E0CE;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .card-header i {
            font-size: 1.8rem;
            background: linear-gradient(145deg, #AA7C5C, #6B3E1C);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4b2e1f;
            margin: 0;
        }

        /* ===== TABLA ESTILOS ===== */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 1rem;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            min-width: 1000px;
        }

        thead th {
            background: #f8f4f0;
            color: #5D4037;
            padding: 1rem 1rem;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 12px;
            text-align: center;
        }

        tbody td {
            padding: 1rem;
            vertical-align: middle;
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            transition: all 0.2s ease;
            text-align: center;
        }

        tbody tr:hover td {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(110, 60, 20, 0.12);
            background: #FFFBF5;
        }

        /* ===== BADGES Y ESTILOS ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(145deg, #8B4513, #A0522D);
            padding: 6px 14px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
            box-shadow: 0 2px 8px rgba(139,69,19,.3);
        }

        .badge-light {
            background: #ead7c6;
            color: #4b2e1f;
        }

        /* ===== COMBO CARD ===== */
        .combo-card {
            background: linear-gradient(135deg, #fefaf5, #faf5ee);
            border-left: 4px solid #8B4513;
            padding: 0.8rem;
            border-radius: 12px;
            text-align: left;
            transition: all 0.2s ease;
        }

        .combo-card strong {
            font-size: 0.95rem;
            color: #3E2723;
            display: block;
            margin-bottom: 6px;
        }

        .combo-card small {
            font-size: 0.75rem;
            color: #8B6B4F;
            display: block;
            line-height: 1.4;
        }

        .combo-card i {
            color: #8B4513;
            margin-right: 5px;
        }

        /* ===== TIPOS DE RECOMENDACIÓN ===== */
        .recomendado { 
            color: #2e7d32; 
            font-weight: 700; 
            background: #e8f5e9;
            padding: 6px 14px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }
        .potencial { 
            color: #ef6c00; 
            font-weight: 700;
            background: #fff3e0;
            padding: 6px 14px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }
        .exploratorio { 
            color: #616161; 
            font-weight: 700;
            background: #f5f5f5;
            padding: 6px 14px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }
        .fuerte { 
            color: #1b5e20; 
            font-weight: 700;
            background: #c8e6c9;
            padding: 6px 14px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }

        .money {
            font-weight: 700;
            color: #8B4513;
            font-size: 1rem;
        }

        .posicion-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(145deg, #8B4513, #A0522D);
            color: white;
            border-radius: 50%;
            font-weight: 800;
            font-size: 0.9rem;
        }

        /* ===== LISTA DE INTERPRETACIÓN ===== */
        .interpretacion-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .interpretacion-item {
            background: #fefaf5;
            padding: 1rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .interpretacion-item:hover {
            transform: translateX(5px);
            background: #fff5ea;
        }

        .interpretacion-item i {
            font-size: 1.5rem;
        }

        .interpretacion-item .fuerte-icon { color: #1b5e20; }
        .interpretacion-item .recomendado-icon { color: #2e7d32; }
        .interpretacion-item .potencial-icon { color: #ef6c00; }
        .interpretacion-item .exploratorio-icon { color: #616161; }

        .interpretacion-item strong {
            display: block;
            font-size: 0.9rem;
        }

        .interpretacion-item p {
            font-size: 0.75rem;
            color: #8B6B4F;
            margin-top: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #8B6B4F;
            background: #fefaf5;
            border-radius: 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .coffee-cup, .coffee-bean {
                display: none;
            }

            .hero-header {
                padding: 1.5rem;
            }

            .hero-header h1 {
                font-size: 1.5rem;
                flex-wrap: wrap;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 1.2rem;
            }

            .card-header h2 {
                font-size: 1.2rem;
            }

            thead {
                display: none;
            }

            tbody tr {
                display: block;
                margin-bottom: 1rem;
            }

            tbody td {
                display: block;
                text-align: right;
                padding: 0.8rem;
                border-radius: 10px;
            }

            tbody td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #8B4513;
                float: left;
                font-size: 0.8rem;
            }

            .interpretacion-list {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero-header h1 {
                font-size: 1.2rem;
            }
            
            .stat-content .value {
                font-size: 1.5rem;
            }
        }

        /* Animaciones de entrada */
        .card, .stat-card {
            animation: fadeInUp 0.5s ease backwards;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.1s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<!-- Elementos decorativos de cafetería -->
<div class="coffee-decor">
    <div class="coffee-cup cup-1">
        <div class="cup-top"></div>
        <div class="cup-body"><div class="cup-handle"></div></div>
        <div class="steam s1"></div><div class="steam s2"></div><div class="steam s3"></div>
    </div>
    <div class="coffee-cup cup-2">
        <div class="cup-top"></div>
        <div class="cup-body"><div class="cup-handle"></div></div>
        <div class="steam s1"></div><div class="steam s2"></div><div class="steam s3"></div>
    </div>
    <div class="coffee-cup cup-3">
        <div class="cup-top"></div>
        <div class="cup-body"><div class="cup-handle"></div></div>
        <div class="steam s1"></div><div class="steam s2"></div><div class="steam s3"></div>
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

<div class="container">
    <!-- Header -->
    <div class="hero-header">
        <h1>
            <i class="fas fa-cart-plus"></i>
            Análisis de Combos - Market Basket
        </h1>
        <p>
            <i class="fas fa-chart-line"></i>
            Detectamos productos que se compran juntos | Precios sugeridos y descuentos inteligentes
        </p>
    </div>

    <!-- Tarjeta de resumen -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="stat-content">
                <div class="label">Combos detectados</div>
                <div class="value">{{ count($kmeans) }}</div>
            </div>
        </div>
    </div>

    <!-- Tabla de combos -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-ranking-star"></i>
            <h2>Top Combos Detectados</h2>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Combo</th>
                        <th>Frecuencia</th>
                        <th>% Aparición</th>
                        <th>Precio normal</th>
                        <th>Descuento</th>
                        <th>Precio sugerido</th>
                        <th>Ahorro</th>
                        <th>Acción sugerida</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($kmeans as $combo)
                    @php
                        $tipo = $combo['recomendacion_tipo'] ?? 'Combo exploratorio';
                    @endphp
                    <tr>
                        <td data-label="#">
                            <span class="posicion-badge">{{ $combo['posicion'] ?? $loop->iteration }}</span>
                        </td>

                        <td data-label="Combo">
                            <div class="combo-card">
                                <strong><i class="fas fa-tag"></i> {{ $combo['combo'] ?? 'Combo sin nombre' }}</strong>
                                @if(isset($combo['producto_1']))
                                    <small><i class="fas fa-coffee"></i> {{ $combo['producto_1'] }}</small>
                                @endif
                                @if(isset($combo['producto_2']))
                                    <small><i class="fas fa-mug-hot"></i> {{ $combo['producto_2'] }}</small>
                                @endif
                            </div>
                        </td>

                        <td data-label="Frecuencia">
                            <span class="badge">
                                <i class="fas fa-chart-simple"></i>
                                {{ $combo['frecuencia'] ?? 0 }}
                            </span>
                        </td>

                        <td data-label="% Aparición">
                            <span class="badge badge-light">
                                {{ $combo['porcentaje_aparicion'] ?? 0 }}%
                            </span>
                        </td>

                        <td data-label="Precio normal" class="money">
                            ${{ number_format($combo['precio_normal'] ?? 0, 2) }}
                        </td>

                        <td data-label="Descuento">
                            <span class="badge" style="background:#ef6c00;">
                                -{{ $combo['descuento_sugerido'] ?? 0 }}%
                            </span>
                        </td>

                        <td data-label="Precio sugerido" class="money">
                            ${{ number_format($combo['precio_combo_sugerido'] ?? 0, 2) }}
                        </td>

                        <td data-label="Ahorro" class="money">
                            <i class="fas fa-save"></i>
                            ${{ number_format($combo['ahorro_cliente'] ?? 0, 2) }}
                        </td>

                        <td data-label="Acción sugerida">
                            @if($tipo == 'Combo fuerte')
                                <span class="fuerte"><i class="fas fa-fire"></i> {{ $combo['recomendacion'] ?? $tipo }}</span>
                            @elseif($tipo == 'Combo recomendado')
                                <span class="recomendado"><i class="fas fa-thumbs-up"></i> {{ $combo['recomendacion'] ?? $tipo }}</span>
                            @elseif($tipo == 'Combo potencial')
                                <span class="potencial"><i class="fas fa-chart-line"></i> {{ $combo['recomendacion'] ?? $tipo }}</span>
                            @else
                                <span class="exploratorio"><i class="fas fa-flask"></i> {{ $combo['recomendacion'] ?? $tipo }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <p>No se encontraron combos en el historial de ventas.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Interpretación -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-circle-info"></i>
            <h2>Interpretación de Resultados</h2>
        </div>

        <div class="interpretacion-list">
            <div class="interpretacion-item">
                <i class="fas fa-fire fuerte-icon"></i>
                <div>
                    <strong class="fuerte">Combo fuerte</strong>
                    <p>Alta frecuencia de compra. Ideal como promoción principal.</p>
                </div>
            </div>
            <div class="interpretacion-item">
                <i class="fas fa-thumbs-up recomendado-icon"></i>
                <div>
                    <strong class="recomendado">Combo recomendado</strong>
                    <p>Buena frecuencia. Descuento moderado recomendado.</p>
                </div>
            </div>
            <div class="interpretacion-item">
                <i class="fas fa-chart-line potencial-icon"></i>
                <div>
                    <strong class="potencial">Combo potencial</strong>
                    <p>Prueba como promoción temporal para validar.</p>
                </div>
            </div>
            <div class="interpretacion-item">
                <i class="fas fa-flask exploratorio-icon"></i>
                <div>
                    <strong class="exploratorio">Combo exploratorio</strong>
                    <p>Requiere más historial de ventas para validar.</p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>