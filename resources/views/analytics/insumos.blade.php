<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CaféSoft | Comparación por Insumo </title>
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
        .cup-3 { top: 40%; right: 5%; transform: scale(0.6) rotate(-5deg); }

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
        .bean-1 { top: 12%; left: 3%; }
        .bean-2 { bottom: 18%; right: 6%; }
        .bean-3 { top: 55%; left: 85%; }

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
        .particle-1 { top: 20%; left: 10%; }
        .particle-2 { top: 60%; right: 15%; }

        @keyframes particleFloat {
            0% { transform: translateY(0) scale(1); opacity: 0.3; }
            100% { transform: translateY(-100vh) scale(0); opacity: 0; }
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .container {
            max-width: 1400px;
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
            content: "☕";
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
            border-spacing: 0 12px;
            min-width: 800px;
        }

        thead th {
            background: #f8f4f0;
            color: #5D4037;
            padding: 1rem 1.2rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 12px;
        }

        tbody td {
            padding: 1.2rem;
            vertical-align: top;
            background: white;
            border-radius: 16px;
            box-shadow: 0 3px 10px rgba(0,0,0,.04);
            transition: all 0.2s ease;
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
            gap: 8px;
            background: linear-gradient(145deg, #8B4513, #A0522D);
            padding: 8px 18px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            box-shadow: 0 2px 8px rgba(139,69,19,.3);
        }

        .insumo-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3E2723;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .insumo-name i {
            color: #8B4513;
            font-size: 1.3rem;
        }

        /* ===== RANKING DE PRODUCTOS ===== */
        .ranking-item {
            background: #fefaf5;
            padding: 0.9rem;
            border-radius: 12px;
            margin-bottom: 0.8rem;
            border-left: 4px solid #8B4513;
            transition: all 0.2s ease;
        }

        .ranking-item:hover {
            background: #fff5ea;
            transform: translateX(5px);
        }

        .ranking-item strong {
            font-size: 0.95rem;
            color: #3E2723;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .ranking-details {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 8px;
            font-size: 0.85rem;
        }

        .ranking-details span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f0e4d8;
            padding: 4px 12px;
            border-radius: 20px;
            color: #5D4037;
        }

        .ranking-details i {
            color: #8B4513;
            font-size: 0.8rem;
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
                padding: 1rem;
                border-radius: 12px;
            }

            tbody td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #8B4513;
                display: block;
                margin-bottom: 0.5rem;
                font-size: 0.8rem;
                text-transform: uppercase;
            }
        }

        @media (max-width: 480px) {
            .ranking-details {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* Animaciones de entrada */
        .card, .stat-card {
            animation: fadeInUp 0.5s ease backwards;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
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
    <div class="coffee-bean bean-1"></div>
    <div class="coffee-bean bean-2"></div>
    <div class="coffee-bean bean-3"></div>
    <div class="particle particle-1"></div>
    <div class="particle particle-2"></div>
</div>

<div class="container">
    <!-- Header -->
    <div class="hero-header">
        <h1>
            <i class="fas fa-chart-map"></i>
            MapReduce - Comparación por Insumo
        </h1>
        <p>
            <i class="fas fa-cubes"></i>
            Agrupación de ventas por insumo | Ranking de consumo por producto
        </p>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-flask"></i>
            </div>
            <div class="stat-content">
                <div class="label">Insumos analizados</div>
                <div class="value">{{ count($mapreduce) }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="label">Consumo total acumulado</div>
                <div class="value">{{ collect($mapreduce)->sum('total_consumido') }}</div>
            </div>
        </div>
    </div>

    <!-- Tabla principal -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table-list"></i>
            <h2>Detalle de consumo por insumo</h2>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Total consumido</th>
                        <th>Ranking de productos que usan ese insumo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapreduce as $item)
                        <tr>
                            <td data-label="Insumo">
                                <div class="insumo-name">
                                    <i class="fas fa-leaf"></i>
                                    <strong>{{ $item['insumo'] ?? 'Sin insumo' }}</strong>
                                </div>
                            </td>
                            <td data-label="Total consumido">
                                <span class="badge">
                                    <i class="fas fa-chart-simple"></i>
                                    {{ $item['total_consumido'] ?? 0 }}
                                </span>
                            </td>
                            <td data-label="Ranking de productos">
                                @forelse(($item['ranking_productos'] ?? []) as $producto)
                                    <div class="ranking-item">
                                        <strong>
                                            <i class="fas fa-trophy" style="color: #FFD700;"></i>
                                            #{{ $producto['posicion'] ?? '-' }}
                                            {{ $producto['producto'] ?? 'Producto sin nombre' }}
                                        </strong>
                                        <div class="ranking-details">
                                            <span>
                                                <i class="fas fa-box"></i>
                                                Vendidos: {{ $producto['cantidad_vendida'] ?? 0 }}
                                            </span>
                                            <span>
                                                <i class="fas fa-coffee"></i>
                                                Consumo estimado: {{ $producto['consumo_estimado'] ?? 0 }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state" style="padding: 1rem;">
                                        <i class="fas fa-info-circle"></i>
                                        Sin productos asociados.
                                    </div>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <i class="fas fa-database"></i>
                                    <p>No hay datos de MapReduce disponibles.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>