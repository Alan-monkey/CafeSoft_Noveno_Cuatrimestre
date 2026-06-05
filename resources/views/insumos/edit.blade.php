@extends('layouts.app')
@section('content')

<div class="inventario-container">
    <div class="container py-4">

        <div class="inventario-header">
            <div class="header-icon"><i class="fas fa-edit"></i></div>
            <div class="header-title">
                <h4><i class="fas fa-coffee"></i> Editar Insumo</h4>
                <p>Modificar datos del insumo #{{ $insumo->id_insumo }}</p>
            </div>
            <div class="coffee-decoration-header" style="margin-left:auto">
                <span>📦</span><span>☕</span>
            </div>
            <a href="{{ route('inventario.index') }}" class="btn-nuevo">
                <i class="fas fa-arrow-left me-2"></i> Regresar
            </a>
        </div>

        <div class="inventario-card">
            <form action="{{ route('insumos.update', $insumo->id_insumo) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Nombre del insumo <span class="text-danger">*</span></label>
                        <input type="text" name="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $insumo->nombre) }}" required>
                        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                            <option value="piezas"  {{ old('tipo', $insumo->tipo)=='piezas'  ? 'selected' : '' }}>Piezas</option>
                            <option value="gramos"  {{ old('tipo', $insumo->tipo)=='gramos'  ? 'selected' : '' }}>Gramos</option>
                            <option value="litros"  {{ old('tipo', $insumo->tipo)=='litros'  ? 'selected' : '' }}>Litros</option>
                        </select>
                        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad" step="0.01" min="0"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad', $insumo->cantidad) }}" required>
                        @error('cantidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Cantidad mínima <span class="text-danger">*</span></label>
                        <input type="number" name="cantidad_minima" step="0.01" min="0"
                               class="form-control @error('cantidad_minima') is-invalid @enderror"
                               value="{{ old('cantidad_minima', $insumo->cantidad_minima) }}" required>
                        @error('cantidad_minima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha de caducidad <span class="text-danger">*</span></label>
                        <input type="text" name="caducidad" id="caducidad"
                            class="form-control @error('caducidad') is-invalid @enderror"
                            placeholder="YYYY-MM-DD o NA"
                            value="{{ old('caducidad', $insumo->caducidad === 'NA' ? 'NA' : \Carbon\Carbon::parse($insumo->caducidad)->format('Y-m-d')) }}"
                            required>
                        <small style="color:#8B6B4F;font-size:.8rem;">
                            Escribe una fecha (2026-12-31) o <strong>NA</strong> si no aplica
                        </small>
                        @error('caducidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>


                    <div class="col-md-6">
                        <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                        <input type="text" name="proveedor"
                               class="form-control @error('proveedor') is-invalid @enderror"
                               value="{{ old('proveedor', $insumo->proveedor) }}" required>
                        @error('proveedor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Precio unitario <span class="text-danger">*</span></label>
                        <input type="number" name="precio_unitario" step="0.01" min="0"
                               class="form-control @error('precio_unitario') is-invalid @enderror"
                               value="{{ old('precio_unitario', $insumo->precio_unitario) }}" required>
                        @error('precio_unitario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 text-end mt-2">
                        <a href="{{ route('inventario.index') }}" class="btn-cancel-form me-2">Cancelar</a>
                        <button type="submit" class="btn-guardar">
                            <i class="fas fa-save me-2"></i> Guardar cambios
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

@include('insumos._styles')
@endsection
