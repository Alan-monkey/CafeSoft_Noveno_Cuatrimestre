@extends('layouts.app')
@section('content')

<div class="producto-create-container">
    <div class="coffee-elements">
        <div class="coffee-cup cup-1">
            <div class="cup-top white-cup"></div>
            <div class="cup-body white-cup"></div>
            <div class="cup-handle white-handle"></div>
            <div class="steam s1"></div><div class="steam s2"></div><div class="steam s3"></div>
        </div>
        <div class="coffee-bean bean-1"></div>
        <div class="coffee-bean bean-2"></div>
        <div class="coffee-bean bean-3"></div>
    </div>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="producto-card">
                    <div class="producto-card-header">
                        <div class="header-icon"><i class="fas fa-edit"></i></div>
                        <div class="header-title">
                            <h4><i class="fas fa-coffee"></i> Editar producto</h4>
                            <p>Actualiza la información del producto</p>
                        </div>
                        <div class="coffee-decoration-header">
                            <span>☕</span><span>✏️</span><span>☕</span>
                        </div>
                    </div>

                    <div class="producto-card-body">
                        <form method="POST" action="{{ route('productos.update', $producto->_id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                    <!-- Campo Insumos -->
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-boxes"></i> Insumos requeridos
                        </label>
                        <div class="insumos-selector-wrapper">
                            <div class="insumos-search-box" id="insumosSearchBox" onclick="toggleInsumosList()">
                                <span id="insumosPlaceholder">Selecciona los insumos necesarios...</span>
                                <i class="fas fa-chevron-down" id="insumosChevron"></i>
                            </div>
                            <div class="insumos-dropdown" id="insumosDropdown">
                                <input type="text" class="insumos-filter" id="insumosFilter"
                                    placeholder="Buscar insumo..." oninput="filtrarInsumos()">
                                <div class="insumos-list" id="insumosList">
                                    @foreach($insumos as $insumo)
                                    <div class="insumo-item" onclick="toggleInsumo('{{ $insumo->_id }}', '{{ addslashes($insumo->nombre) }}', '{{ $insumo->tipo ?? 'piezas' }}')">
                                        <input type="checkbox" class="insumo-checkbox"
                                            id="insumo_{{ $insumo->_id }}" value="{{ $insumo->_id }}">
                                        <label for="insumo_{{ $insumo->_id }}" class="insumo-label">
                                            <span class="insumo-nombre">{{ $insumo->nombre }}</span>
                                            <span class="insumo-tipo">{{ $insumo->tipo ?? '' }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div id="insumosHidden"></div>
                        <div class="insumos-cantidades" id="insumosCantidades"></div>
                        <small class="form-text-modern">
                            <i class="fas fa-info-circle"></i> Selecciona los insumos e indica la cantidad de cada uno
                        </small>
                    </div>
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-cookie-bite"></i> Nombre del producto
                                </label>
                                <div class="input-wrapper-modern">
                                    <input type="text" name="nombre" class="form-control-modern"
                                        value="{{ $producto->nombre }}" required>
                                    <span class="focus-border"></span>
                                </div>
                            </div>


                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-tag"></i> Precio
                                </label>
                                <div class="input-wrapper-modern">
                                    <input type="number" step="0.01" name="precio" class="form-control-modern"
                                           value="{{ $producto->precio }}" required>
                                    <span class="focus-border"></span>
                                </div>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-pencil-alt"></i> Descripción
                                </label>
                                <div class="input-wrapper-modern">
                                    <textarea name="descripcion" class="form-control-modern" rows="4" required>{{ $producto->descripcion }}</textarea>
                                    <span class="focus-border"></span>
                                </div>
                            </div>

                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-image"></i> Nueva imagen (opcional)
                                </label>
                                <div class="file-input-wrapper-modern">
                                    <input type="file" name="imagen" accept="image/*" id="fileInput">
                                    <div class="file-input-button-modern" id="fileButton">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Seleccionar imagen</span>
                                    </div>
                                </div>
                                @if($producto->imagen)
                                <div style="margin-top:10px">
                                    <img src="{{ asset('storage/' . $producto->imagen) }}"
                                         style="width:80px;height:80px;object-fit:cover;border-radius:12px;border:2px solid #e8d5c0">
                                    <small class="form-text-modern" style="margin-left:8px">Imagen actual</small>
                                </div>
                                @endif
                            </div>

                            <div class="d-flex gap-3">
                                <a href="{{ route('productos.leer') }}" class="btn-cancelar-editar">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </a>
                                <button type="submit" class="btn-submit-modern" style="flex:1">
                                    <i class="fas fa-save"></i> Guardar cambios
                                    <span class="btn-overlay"></span>
                                </button>
                            </div>
                        </form>

                        @if(session('success'))
                        <div class="alert-modern alert-success-modern mt-4">
                            <i class="fas fa-check-circle"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert-modern mt-4" style="background:#f8d7da;border-left:6px solid #dc3545;color:#721c24;padding:18px 20px;border-radius:15px;display:flex;align-items:center;gap:12px">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="producto-card-footer">
                        <div class="coffee-beans-footer">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const fileButton = document.getElementById('fileButton').querySelector('span');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Seleccionar imagen (opcional)';
            fileButton.textContent = fileName;
        });
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.insumos-selector-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeInsumosList();
        }
    });

    // Pre-cargar insumos existentes del producto
    const insumosActuales = @json($producto->insumos ?? []);
    const cantidadesActuales = @json($producto->insumos_cantidad ?? []);

    insumosActuales.forEach(id => {
        const checkbox = document.getElementById('insumo_' + id);
        if (!checkbox) return;

        const item = checkbox.closest('.insumo-item');
        const nombre = item.querySelector('.insumo-nombre').textContent.trim();
        const tipo = item.querySelector('.insumo-tipo').textContent.trim() || 'piezas';
        const cantidad = cantidadesActuales[id] ?? '';

        insumosSeleccionados[id] = { nombre, tipo, cantidad };
        checkbox.checked = true;
        item.classList.add('selected');
    });

    renderCantidades();
    renderHiddenInputs();
    updatePlaceholder();
});



// { id: { nombre, tipo, cantidad } }
const insumosSeleccionados = {};

function toggleInsumosList() {
    const dropdown = document.getElementById('insumosDropdown');
    const isOpen = dropdown.classList.contains('open');
    if (isOpen) {
        closeInsumosList();
    } else {
        dropdown.classList.add('open');
        document.getElementById('insumosChevron').classList.add('rotated');
        document.getElementById('insumosSearchBox').classList.add('open');
        document.getElementById('insumosFilter').focus();
    }
}

function closeInsumosList() {
    document.getElementById('insumosDropdown').classList.remove('open');
    document.getElementById('insumosChevron').classList.remove('rotated');
    document.getElementById('insumosSearchBox').classList.remove('open');
}

function toggleInsumo(id, nombre, tipo) {
    const checkbox = document.getElementById('insumo_' + id);
    const item = checkbox.closest('.insumo-item');

    if (insumosSeleccionados[id]) {
        delete insumosSeleccionados[id];
        checkbox.checked = false;
        item.classList.remove('selected');
    } else {
        insumosSeleccionados[id] = { nombre, tipo, cantidad: '' };
        checkbox.checked = true;
        item.classList.add('selected');
    }

    renderCantidades();
    renderHiddenInputs();
    updatePlaceholder();
}

function renderCantidades() {
    const container = document.getElementById('insumosCantidades');
    container.innerHTML = '';

    Object.entries(insumosSeleccionados).forEach(([id, data]) => {
        const unidad = getUnidad(data.tipo);
        const step = data.tipo === 'piezas' ? '1' : '0.1';

        const row = document.createElement('div');
        row.className = 'insumo-cantidad-row';
        row.innerHTML = `
            <div class="insumo-cantidad-info">
                <span class="insumo-cantidad-nombre">${data.nombre}</span>
                <span class="insumo-cantidad-tipo">${data.tipo}</span>
            </div>
            <div class="insumo-cantidad-input-wrap">
                <input type="number" 
                    class="insumo-cantidad-input"
                    placeholder="0"
                    min="0.001"
                    step="any"
                    value="${data.cantidad}"
                    oninput="setCantidad('${id}', this.value)"
                    required>
                <span class="insumo-unidad-badge">${unidad}</span>
            </div>
            <button type="button" class="insumo-quitar-btn" onclick="quitarInsumo('${id}')">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(row);
    });
}

function getUnidad(tipo) {
    const tipos = {
        'gramos': 'g',
        'litros': 'L',
        'piezas': 'pz',
        'kilogramos': 'kg',
        'mililitros': 'ml',
    };
    return tipos[tipo?.toLowerCase()] ?? tipo ?? 'u';
}

function setCantidad(id, valor) {
    if (insumosSeleccionados[id]) {
        insumosSeleccionados[id].cantidad = valor;
        renderHiddenInputs();
    }
}

function quitarInsumo(id) {
    delete insumosSeleccionados[id];
    const checkbox = document.getElementById('insumo_' + id);
    if (checkbox) {
        checkbox.checked = false;
        checkbox.closest('.insumo-item').classList.remove('selected');
    }
    renderCantidades();
    renderHiddenInputs();
    updatePlaceholder();
}

function renderHiddenInputs() {
    const container = document.getElementById('insumosHidden');
    container.innerHTML = '';
    Object.entries(insumosSeleccionados).forEach(([id, data]) => {
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'insumos[]';
        inputId.value = id;
        container.appendChild(inputId);

        const inputCantidad = document.createElement('input');
        inputCantidad.type = 'hidden';
        inputCantidad.name = `insumos_cantidad[${id}]`;
        inputCantidad.value = data.cantidad;
        container.appendChild(inputCantidad);
    });
}

function updatePlaceholder() {
    const placeholder = document.getElementById('insumosPlaceholder');
    const count = Object.keys(insumosSeleccionados).length;
    if (count === 0) {
        placeholder.textContent = 'Selecciona los insumos necesarios...';
        placeholder.classList.remove('has-selection');
    } else {
        placeholder.textContent = count + ' insumo(s) seleccionado(s)';
        placeholder.classList.add('has-selection');
    }
}

function filtrarInsumos() {
    const filtro = document.getElementById('insumosFilter').value.toLowerCase();
    document.querySelectorAll('.insumo-item').forEach(item => {
        const nombre = item.querySelector('.insumo-nombre').textContent.toLowerCase();
        item.style.display = nombre.includes(filtro) ? 'flex' : 'none';
    });
}
</script>


<style>
    .producto-create-container { position:relative;min-height:100vh;background:linear-gradient(145deg,#faf0e6 0%,#f5e6d3 100%);font-family:'Poppins','Segoe UI',sans-serif;padding:20px 0;overflow-x:hidden; }
    .coffee-elements { position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0; }
    .coffee-cup { position:absolute;opacity:.15; }
    .cup-1 { top:30px;left:30px;transform:scale(.7); }
    .white-cup { background:linear-gradient(145deg,#ffffff,#f8f8f8) !important; }
    .white-handle { border-color:#f0f0f0 !important;border-right:6px solid #ffffff !important; }
    .cup-top { width:60px;height:15px;border-radius:50%;background:linear-gradient(145deg,#ffffff,#f0f0f0); }
    .cup-body { width:50px;height:45px;border-radius:0 0 25px 25px;background:linear-gradient(145deg,#ffffff,#f5f5f5);top:-7px;position:relative; }
    .cup-handle { width:18px;height:30px;border:5px solid #f0f0f0;border-left:none;border-radius:0 15px 15px 0;position:absolute;right:-15px;top:10px; }
    .steam { position:absolute;background:rgba(255,255,255,.5);border-radius:50%;animation:steam 3s infinite; }
    .s1{width:10px;height:10px;top:-15px;left:15px} .s2{width:8px;height:8px;top:-20px;left:25px;animation-delay:.5s} .s3{width:6px;height:6px;top:-18px;left:35px;animation-delay:1s}
    @keyframes steam{0%,100%{transform:translateY(0) scale(1);opacity:.5}50%{transform:translateY(-10px) scale(1.2);opacity:.2}}
    .coffee-bean{position:absolute;width:15px;height:7px;background:#8B4513;border-radius:50%;opacity:.1;animation:float 20s infinite linear;transform:rotate(45deg)}
    .bean-1{top:15%;left:5%} .bean-2{bottom:20%;right:5%;animation-delay:5s} .bean-3{top:40%;left:8%;animation-delay:8s}
    @keyframes float{from{transform:translateY(0) rotate(45deg);opacity:.1}to{transform:translateY(-100vh) rotate(405deg);opacity:0}}

    .producto-card { background:rgba(255,255,255,.98);border-radius:30px;box-shadow:0 20px 40px rgba(139,69,19,.15);position:relative;z-index:10;overflow:hidden;border:1px solid rgba(255,255,255,.3);animation:fadeInUp .8s ease; }
    @keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    .producto-card-header { background:linear-gradient(135deg,#8B4513 0%,#A0522D 100%);color:white;padding:25px 30px;display:flex;align-items:center;gap:20px;position:relative;overflow:hidden; }
    .header-icon { width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:2rem; }
    .header-title h4 { margin:0;font-weight:700;font-size:1.5rem; }
    .header-title p { margin:5px 0 0;opacity:.9;font-size:.9rem; }
    .coffee-decoration-header { margin-left:auto;font-size:1.5rem; }
    .coffee-decoration-header span { margin:0 5px; }
    .producto-card-body { padding:35px; }
    .producto-card-footer { padding:15px;text-align:center;border-top:2px dashed #e8d5c0; }
    .coffee-beans-footer { display:flex;justify-content:center;gap:10px; }
    .coffee-beans-footer span { width:12px;height:18px;background:#8B4513;border-radius:50%;transform:rotate(45deg);display:inline-block;opacity:.5; }

    .form-group-modern { margin-bottom:25px; }
    .form-label-modern { display:block;margin-bottom:10px;color:#5D4037;font-weight:600;font-size:.95rem;text-transform:uppercase;letter-spacing:.5px; }
    .form-label-modern i { margin-right:8px;color:#8B4513; }
    .input-wrapper-modern { position:relative; }
    .form-control-modern { width:100%;padding:14px 20px;border:2px solid #e8d5c0;border-radius:15px;font-size:1rem;transition:all .3s ease;background:white;color:#3E2723; }
    .form-control-modern:focus { outline:none;border-color:#8B4513;box-shadow:0 0 0 4px rgba(139,69,19,.1); }
    textarea.form-control-modern { min-height:120px;resize:vertical; }
    .focus-border { position:absolute;bottom:0;left:50%;width:0;height:3px;background:linear-gradient(90deg,#8B4513,#C97C5D);transition:all .3s ease;transform:translateX(-50%);border-radius:3px; }
    .form-control-modern:focus ~ .focus-border { width:100%; }

    .file-input-wrapper-modern { position:relative;overflow:hidden;width:100%; }
    .file-input-wrapper-modern input[type=file] { position:absolute;left:0;top:0;opacity:0;width:100%;height:100%;cursor:pointer;z-index:10; }
    .file-input-button-modern { background:linear-gradient(145deg,#f0e4d5,#e8d5c0);border:2px dashed #8B4513;border-radius:15px;padding:25px;text-align:center;color:#8B4513;font-weight:500;display:flex;align-items:center;justify-content:center;gap:12px;cursor:pointer; }
    .file-input-button-modern i { font-size:2rem; }
    .form-text-modern { display:block;margin-top:8px;color:#B2967D;font-size:.9rem; }

    .btn-submit-modern { background:linear-gradient(135deg,#8B4513 0%,#A0522D 100%);color:white;border:none;border-radius:15px;padding:16px 30px;font-size:1.1rem;font-weight:600;cursor:pointer;transition:all .3s ease;width:100%;text-transform:uppercase;box-shadow:0 8px 20px rgba(74,44,44,.3);position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;gap:10px; }
    .btn-submit-modern:hover { transform:translateY(-3px);box-shadow:0 15px 30px rgba(74,44,44,.4); }
    .btn-overlay { position:absolute;top:50%;left:50%;width:0;height:0;border-radius:50%;background:rgba(255,255,255,.3);transform:translate(-50%,-50%);transition:width .5s,height .5s; }
    .btn-submit-modern:hover .btn-overlay { width:300px;height:300px; }

    .btn-cancelar-editar { background:#f0e4d5;color:#8B4513;border:none;border-radius:15px;padding:16px 24px;font-size:1rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all .3s; }
    .btn-cancelar-editar:hover { background:#e8d5c0;color:#8B4513; }

    .d-flex { display:flex; }
    .gap-3 { gap:1rem; }

    .alert-modern { padding:18px 20px;border-radius:15px;display:flex;align-items:center;gap:12px;animation:slideIn .5s ease; }
    .alert-success-modern { background:linear-gradient(145deg,#d4edda,#c3e6cb);border-left:6px solid #28a745;color:#155724; }
    @keyframes slideIn{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
    /* ===== CANTIDADES DE INSUMOS ===== */
.insumos-cantidades {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.insumo-cantidad-row {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #faf0e6;
    border: 1px solid #e8d5c0;
    border-radius: 12px;
    padding: 10px 14px;
    animation: tagAppear 0.2s ease;
}

.insumo-cantidad-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.insumo-cantidad-nombre {
    font-weight: 600;
    color: #3E2723;
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.insumo-cantidad-tipo {
    font-size: 0.78rem;
    color: #B2967D;
}

.insumo-cantidad-input-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}

.insumo-cantidad-input {
    width: 80px;
    padding: 8px 10px;
    border: 2px solid #e8d5c0;
    border-radius: 10px;
    font-size: 0.95rem;
    color: #3E2723;
    text-align: center;
    transition: border-color 0.2s;
}

.insumo-cantidad-input:focus {
    outline: none;
    border-color: #8B4513;
}

.insumo-unidad-badge {
    background: linear-gradient(135deg, #8B4513, #A0522D);
    color: white;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    min-width: 32px;
    text-align: center;
}

.insumo-quitar-btn {
    background: none;
    border: none;
    color: #B2967D;
    cursor: pointer;
    font-size: 0.9rem;
    padding: 4px 6px;
    border-radius: 6px;
    transition: all 0.2s;
}

.insumo-quitar-btn:hover {
    color: #dc3545;
    background: #f8d7da;
}

@keyframes tagAppear {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

@endsection
