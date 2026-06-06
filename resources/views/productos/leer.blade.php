@extends('layouts.app')
@section('content')

<div class="productos-container">
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
        <div class="coffee-cup cup-3">
            <div class="cup-top white-cup"></div>
            <div class="cup-body white-cup"></div>
            <div class="cup-handle white-handle"></div>
            <div class="steam s1"></div><div class="steam s2"></div><div class="steam s3"></div>
        </div>
        <div class="coffee-bean bean-1"></div><div class="coffee-bean bean-2"></div>
        <div class="coffee-bean bean-3"></div><div class="coffee-bean bean-4"></div>
        <div class="coffee-bean bean-5"></div>
        <div class="particle particle-1"></div><div class="particle particle-2"></div>
        <div class="particle particle-3"></div>
    </div>

    <div class="container py-4">
        <div class="coffee-decoration">
            <span>☕</span> <span>☕</span> <span>☕</span>
        </div>

        <div class="productos-header">
            <div class="header-icon"><i class="fas fa-box-open"></i></div>
            <div class="header-title">
                <h4><i class="fas fa-coffee"></i> Lista de productos</h4>
                <p>Catálogo completo de nuestra cafetería</p>
            </div>
            <div class="coffee-decoration-header">
                <span>☕</span><span>📋</span><span>☕</span>
            </div>
        </div>

        <div class="productos-table-container">
            <table class="table productos-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> Nombre</th>
                        <th><i class="fas fa-dollar-sign"></i> Precio</th>
                        <th><i class="fas fa-align-left"></i> Descripción</th>
                        <th><i class="fas fa-image"></i> Imagen</th>
                        <th><i class="fas fa-list-ul"></i> Ingredientes</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $p)
                    <tr>
                        <td data-label="Nombre">
                            <div class="producto-nombre-cell">
                                <i class="fas fa-cookie-bite"></i>
                                <strong>{{ $p->nombre }}</strong>
                            </div>
                        </td>
                        <td data-label="Precio">
                            <span class="precio-badge">${{ number_format($p->precio, 2) }}</span>
                        </td>
                        <td data-label="Descripción">
                            <div class="descripcion-cell">{{ $p->descripcion }}</div>
                        </td>
                        <td data-label="Imagen">
                            @if($p->imagen)
                                <div class="imagen-container">
                                    <img src="{{ asset('storage/' . $p->imagen) }}" alt="{{ $p->nombre }}" class="producto-imagen">
                                </div>
                            @else
                                <span class="sin-imagen-badge">
                                    <i class="fas fa-image"></i> Sin imagen
                                </span>
                            @endif
                        </td>
                        <td data-label="Ingredientes">
                            @php
                                $insumos = $p->insumos ?? [];
                                $cantidades = $p->insumos_cantidad ?? [];
                            @endphp
                            @if(empty($insumos))
                                <span class="sin-ingredientes">Sin ingredientes</span>
                            @else
                                <div class="ingredientes-lista">
                                    @foreach($insumos as $insumoId)
                                        @php
                                            $insumoObj = \App\Models\Insumo::find($insumoId);
                                            $nombre = $insumoObj ? $insumoObj->nombre : $insumoId;
                                            $tipo = $insumoObj ? ($insumoObj->tipo ?? '') : '';
                                            $cantidad = $cantidades[$insumoId] ?? null;
                                            $unidades = ['gramos'=>'g','litros'=>'L','piezas'=>'pz','kilogramos'=>'kg','mililitros'=>'ml'];
                                            $unidad = $unidades[strtolower($tipo)] ?? $tipo;
                                        @endphp
                                        <span class="ingrediente-chip">
                                            {{ $nombre }}
                                            @if($cantidad)
                                                <span class="ingrediente-cantidad">{{ $cantidad }}{{ $unidad }}</span>
                                            @else
                                                <span class="ingrediente-na">NA</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        <td data-label="Acciones">
                            <a href="{{ route('productos.editar', $p->_id) }}" class="btn-editar-producto">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="coffee-decoration" style="margin-top: 30px;">
            <span>☕</span> <span>☕</span> <span>☕</span>
        </div>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal-overlay" id="modalOverlay" onclick="cerrarModal()"></div>
<div class="modal-editar" id="modalEditar">
    <div class="modal-header-editar">
        <div class="modal-icon"><i class="fas fa-edit"></i></div>
        <div>
            <h5><i class="fas fa-coffee"></i> Editar producto</h5>
            <p>Actualiza la información del producto</p>
        </div>
        <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body-editar">
        <form id="formEditar" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group-modal">
                <label><i class="fas fa-cookie-bite"></i> Nombre</label>
                <input type="text" name="nombre" id="editNombre" class="input-modal" required>
            </div>
            <div class="form-group-modal">
                <label><i class="fas fa-tag"></i> Precio</label>
                <input type="number" step="0.01" name="precio" id="editPrecio" class="input-modal" required>
            </div>
            <div class="form-group-modal">
                <label><i class="fas fa-pencil-alt"></i> Descripción</label>
                <textarea name="descripcion" id="editDescripcion" class="input-modal" rows="3" required></textarea>
            </div>
            <div class="form-group-modal">
                <label><i class="fas fa-image"></i> Nueva imagen (opcional)</label>
                <div class="file-modal-wrapper">
                    <input type="file" name="imagen" accept="image/*" id="editImagen"
                        style="position:absolute;opacity:0;width:100%;height:100%;cursor:pointer;z-index:10;">
                    <div class="file-modal-btn">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span id="editImagenNombre">Seleccionar imagen...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer-editar">
                <button type="button" class="btn-cancelar-modal" onclick="cerrarModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-guardar-modal">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
        @if(session('success'))
        <div class="alert-modal-success mt-3">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('editImagen').addEventListener('change', function() {
            const nombre = this.files[0] ? this.files[0].name : 'Seleccionar imagen...';
            document.getElementById('editImagenNombre').textContent = nombre;
        });

        document.addEventListener('click', function(e) {
            if (!document.getElementById('modalEditar').contains(e.target) &&
                !e.target.classList.contains('btn-editar-producto') &&
                !e.target.closest('.btn-editar-producto')) {
                cerrarModal();
            }
        });
    });

    function abrirModal(id, nombre, precio, descripcion) {
        const baseUrl = "{{ url('/productos') }}";
        document.getElementById('formEditar').action = baseUrl + '/' + id;
        document.getElementById('editNombre').value = nombre;
        document.getElementById('editPrecio').value = precio;
        document.getElementById('editDescripcion').value = descripcion;
        document.getElementById('editImagenNombre').textContent = 'Seleccionar imagen...';
        document.getElementById('modalOverlay').style.display = 'block';
        document.getElementById('modalEditar').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        document.getElementById('modalEditar').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
        document.body.style.overflow = '';
    }

</script>

<style>
    .productos-container { position: relative; min-height: 100vh; background: linear-gradient(145deg, #faf0e6 0%, #f5e6d3 100%); font-family: 'Segoe UI', system-ui, sans-serif; padding: 20px 0; overflow-x: hidden; }
    .coffee-elements { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
    .coffee-cup { position: absolute; opacity: 0.15; filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1)); }
    .cup-1 { top: 30px; left: 30px; transform: scale(0.7); }
    .cup-2 { bottom: 30px; right: 30px; transform: scale(0.7) rotate(-10deg); }
    .cup-3 { top: 50%; right: 40px; transform: scale(0.6) translateY(-50%); }
    .white-cup { background: linear-gradient(145deg, #ffffff, #f8f8f8) !important; }
    .white-handle { border-color: #f0f0f0 !important; border-right: 6px solid #ffffff !important; }
    .cup-top { width: 60px; height: 15px; border-radius: 50%; background: linear-gradient(145deg, #ffffff, #f0f0f0); }
    .cup-body { width: 50px; height: 45px; border-radius: 0 0 25px 25px; background: linear-gradient(145deg, #ffffff, #f5f5f5); top: -7px; position: relative; }
    .cup-handle { width: 18px; height: 30px; border: 5px solid #f0f0f0; border-left: none; border-radius: 0 15px 15px 0; position: absolute; right: -15px; top: 10px; }
    .steam { position: absolute; background: rgba(255,255,255,0.5); border-radius: 50%; animation: steam 3s infinite; }
    .s1 { width: 10px; height: 10px; top: -15px; left: 15px; }
    .s2 { width: 8px; height: 8px; top: -20px; left: 25px; animation-delay: 0.5s; }
    .s3 { width: 6px; height: 6px; top: -18px; left: 35px; animation-delay: 1s; }
    @keyframes steam { 0%,100%{transform:translateY(0) scale(1);opacity:.5}50%{transform:translateY(-10px) scale(1.2);opacity:.2} }
    .coffee-bean { position: absolute; width: 15px; height: 7px; background: #8B4513; border-radius: 50%; opacity: 0.1; animation: float 20s infinite linear; transform: rotate(45deg); }
    .bean-1{top:15%;left:5%} .bean-2{bottom:20%;right:5%;animation-delay:5s} .bean-3{top:40%;left:8%;animation-delay:8s} .bean-4{bottom:30%;right:8%;animation-delay:12s} .bean-5{top:70%;left:3%;animation-delay:15s}
    @keyframes float{from{transform:translateY(0) rotate(45deg);opacity:.1}to{transform:translateY(-100vh) rotate(405deg);opacity:0}}
    .particle{position:absolute;width:3px;height:3px;background:rgba(139,69,19,.2);border-radius:50%;animation:particle-float 15s infinite linear}
    .particle-1{top:20%;left:15%} .particle-2{top:60%;right:10%;animation-delay:5s} .particle-3{top:80%;left:20%;animation-delay:10s}
    @keyframes particle-float{from{transform:translateY(0) scale(1);opacity:.3}to{transform:translateY(-100vh) scale(0);opacity:0}}

    .productos-header { background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%); color: white; padding: 25px 30px; display: flex; align-items: center; gap: 20px; position: relative; overflow: hidden; border-radius: 30px 30px 0 0; }
    .productos-header::after { content:''; position:absolute; top:0; right:0; width:200px; height:100%; background:linear-gradient(90deg,transparent,rgba(255,255,255,.2)); transform:skewX(-20deg) translateX(100px); animation:shine 3s infinite; }
    @keyframes shine{0%{transform:skewX(-20deg) translateX(100px)}20%{transform:skewX(-20deg) translateX(-200px)}100%{transform:skewX(-20deg) translateX(-200px)}}
    .header-icon { width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:2rem; }
    .header-title h4 { margin:0;font-weight:700;font-size:1.5rem; }
    .header-title p { margin:5px 0 0;opacity:.9;font-size:.9rem; }
    .coffee-decoration-header { margin-left:auto;font-size:1.5rem; }
    .coffee-decoration-header span { margin:0 5px;animation:bounce 2s infinite;display:inline-block; }
    .coffee-decoration-header span:nth-child(2){animation-delay:.3s}
    @keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}

    .productos-table-container { background:rgba(255,255,255,.98);backdrop-filter:blur(10px);border-radius:0 0 30px 30px;padding:30px;box-shadow:0 20px 40px rgba(139,69,19,.15);border:1px solid rgba(255,255,255,.3); }
    .productos-table { width:100%;border-collapse:separate;border-spacing:0 12px; }
    .productos-table thead th { background:linear-gradient(145deg,#f8f4f0,#f0e8e0);color:#5D4037;font-weight:600;padding:16px;border:none;font-size:.95rem;text-transform:uppercase;letter-spacing:.5px;border-radius:16px 16px 0 0; }
    .productos-table thead th i { margin-right:8px;color:#8B4513; }
    .productos-table tbody tr { background:white;border-radius:20px;box-shadow:0 5px 15px rgba(0,0,0,.03);transition:all .3s ease;animation:fadeInUp .5s ease forwards;opacity:0; }
    .productos-table tbody tr:hover { transform:translateY(-4px);box-shadow:0 15px 30px rgba(139,69,19,.15); }
    .productos-table tbody td { padding:18px 16px;border:none;vertical-align:middle; }
    .productos-table tbody tr:nth-child(1){animation-delay:.1s} .productos-table tbody tr:nth-child(2){animation-delay:.2s} .productos-table tbody tr:nth-child(3){animation-delay:.3s} .productos-table tbody tr:nth-child(4){animation-delay:.4s} .productos-table tbody tr:nth-child(5){animation-delay:.5s}

    .producto-nombre-cell{display:flex;align-items:center;gap:12px}
    .producto-nombre-cell i{font-size:1.5rem;color:#8B4513}
    .producto-nombre-cell strong{color:#2c3e50;font-size:1.1rem}
    .precio-badge{background:linear-gradient(135deg,#e6b17e,#f4c542);padding:8px 16px;border-radius:30px;color:#3E2723;font-weight:700;font-size:1.1rem;display:inline-block;box-shadow:0 4px 10px rgba(230,177,126,.3)}
    .descripcion-cell{color:#6b4f3f;line-height:1.6;border-left:4px solid #d9b382;padding-left:15px;font-size:.95rem;max-width:300px}
    .imagen-container{width:90px;height:90px;border-radius:20px;overflow:hidden;box-shadow:0 8px 20px rgba(74,44,44,.2);border:3px solid white;transition:all .3s ease}
    .imagen-container:hover{transform:scale(1.5) translateX(20px);z-index:100;position:relative}
    .producto-imagen{width:100%;height:100%;object-fit:cover}
    .sin-imagen-badge{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:#f0e4d5;border-radius:30px;color:#8B6347;font-style:italic;font-size:.9rem;border:1px dashed #d9b382}

    /* BOTÓN EDITAR */
    .btn-editar-producto { background:linear-gradient(135deg,#8B4513,#A0522D);color:white;border:none;padding:10px 18px;border-radius:12px;font-size:.9rem;font-weight:600;cursor:pointer;transition:all .3s ease;display:inline-flex;align-items:center;gap:7px; }
    .btn-editar-producto:hover { transform:translateY(-2px);box-shadow:0 8px 20px rgba(139,69,19,.35); }

    /* MODAL */
    .modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;backdrop-filter:blur(3px); }
   .modal-editar { display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;border-radius:24px;width:90%;max-width:520px;z-index:1000;overflow:hidden;box-shadow:0 30px 60px rgba(139,69,19,.3); }
    .modal-editar.open { display:block; }
   .modal-header-editar { background:linear-gradient(135deg,#8B4513,#A0522D);color:white;padding:20px 25px;display:flex;align-items:center;gap:15px; }
    .modal-icon { width:50px;height:50px;background:rgba(255,255,255,.2);border-radius:15px;display:flex;align-items:center;justify-content:center;font-size:1.5rem; }
    .modal-header-editar h5 { margin:0;font-weight:700;font-size:1.2rem; }
    .modal-header-editar p { margin:3px 0 0;opacity:.9;font-size:.85rem; }
    .modal-close { margin-left:auto;background:rgba(255,255,255,.2);border:none;color:white;width:36px;height:36px;border-radius:10px;cursor:pointer;font-size:1rem; }
    .modal-close:hover { background:rgba(255,255,255,.35); }
    .modal-body-editar { padding:25px; }
    .form-group-modal { margin-bottom:18px; }
    .form-group-modal label { display:block;margin-bottom:7px;color:#5D4037;font-weight:600;font-size:.9rem;text-transform:uppercase;letter-spacing:.5px; }
    .form-group-modal label i { margin-right:7px;color:#8B4513; }
    .input-modal { width:100%;padding:12px 16px;border:2px solid #e8d5c0;border-radius:12px;font-size:.95rem;color:#3E2723;transition:border-color .3s;background:white; }
    .input-modal:focus { outline:none;border-color:#8B4513;box-shadow:0 0 0 4px rgba(139,69,19,.1); }
    textarea.input-modal { resize:vertical;min-height:80px; }
    .file-modal-wrapper { position:relative; }
    .file-modal-btn { background:#f0e4d5;border:2px dashed #8B4513;border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:10px;color:#8B4513;font-size:.9rem;cursor:pointer; }
    .modal-footer-editar { display:flex;gap:12px;justify-content:flex-end;margin-top:20px; }
    .btn-cancelar-modal { background:#f0e4d5;color:#8B4513;border:none;padding:12px 22px;border-radius:12px;font-weight:600;cursor:pointer; }
    .btn-cancelar-modal:hover { background:#e8d5c0; }
    .btn-guardar-modal { background:linear-gradient(135deg,#8B4513,#A0522D);color:white;border:none;padding:12px 22px;border-radius:12px;font-weight:600;cursor:pointer;transition:all .3s; }
    .btn-guardar-modal:hover { transform:translateY(-2px);box-shadow:0 8px 20px rgba(139,69,19,.35); }
    .alert-modal-success { background:linear-gradient(145deg,#d4edda,#c3e6cb);border-left:5px solid #28a745;color:#155724;padding:14px 18px;border-radius:12px;display:flex;align-items:center;gap:10px; }

    .coffee-decoration { text-align:center;margin-bottom:20px;font-size:2rem;opacity:.5;letter-spacing:10px; }
    .coffee-decoration span { display:inline-block;animation:bounce-slow 3s infinite; }
    .coffee-decoration span:nth-child(2){animation-delay:.5s} .coffee-decoration span:nth-child(3){animation-delay:1s}
    @keyframes bounce-slow{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

    @media(max-width:768px){
        .coffee-cup{display:none}
        .productos-header{flex-direction:column;text-align:center;padding:20px}
        .coffee-decoration-header{margin:0 auto}
        .productos-table thead{display:none}
        .productos-table tbody tr{display:block;margin-bottom:20px}
        .productos-table tbody td{display:block;text-align:right;padding:12px 15px;position:relative;border-bottom:1px solid #eee}
        .productos-table tbody td:before{content:attr(data-label);position:absolute;left:15px;font-weight:600;color:#3E2723;text-transform:uppercase;font-size:.85rem}
        .producto-nombre-cell{justify-content:flex-end}
        .imagen-container:hover{transform:scale(1.2)}
        .modal-editar{width:95%}
        .ingredientes-lista {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    max-width: 220px;
}

.ingrediente-chip {
    background: #f0e4d5;
    border: 1px solid #d9b382;
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 0.8rem;
    color: #5D4037;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
}

.ingrediente-cantidad {
    background: linear-gradient(135deg, #8B4513, #A0522D);
    color: white;
    border-radius: 10px;
    padding: 1px 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.ingrediente-na {
    color: #B2967D;
    font-style: italic;
    font-size: 0.75rem;
}

.sin-ingredientes {
    color: #B2967D;
    font-style: italic;
    font-size: 0.9rem;
}

    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

@endsection
