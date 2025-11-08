@extends('layouts.app')
@section('title', 'Crear Almacén')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Crear Nuevo Almacén</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Información del Almacén</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('my-warehouses.store') }}" method="POST" id="warehouseForm">
            @csrf

            <div class="row">
                <!-- Código -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Código del Almacén <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control @error('codigo') is-invalid @enderror" 
                           name="codigo" 
                           id="codigo"
                           value="{{ old('codigo') }}"
                           placeholder="ej: centro, norte, sur-1"
                           required>
                    @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <i class="feather icon-info"></i> Código único (solo minúsculas, números y guiones)
                    </small>
                </div>

                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Nombre del Almacén <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           name="name" 
                           value="{{ old('name') }}"
                           placeholder="ej: Almacén Central"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Apodo/Alias -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Apodo/Alias (Opcional)
                    </label>
                    <input type="text" 
                           class="form-control @error('nickname') is-invalid @enderror" 
                           name="nickname" 
                           value="{{ old('nickname') }}"
                           placeholder="ej: Centro, Norte">
                    @error('nickname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <i class="feather icon-info"></i> Nombre corto para mostrar
                    </small>
                </div>

                <!-- Ciudad -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Ciudad (Opcional)
                    </label>
                    <select class="form-control @error('city_id') is-invalid @enderror" 
                            name="city_id">
                        <option value="">Seleccionar ciudad</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" 
                                    {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Estado Activo -->
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               name="is_active" 
                               id="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Almacén activo
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        <i class="feather icon-info"></i> Solo los almacenes activos aparecerán en el sistema
                    </small>
                </div>
            </div>

            <hr class="my-3">

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Crear Almacén
                    </button>
                    <a href="{{ route('my-warehouses.index') }}" class="btn btn-secondary">
                        <i class="feather icon-x"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const codigoInput = document.getElementById('codigo');
    const form = document.getElementById('warehouseForm');
    
    if (codigoInput) {
        // Función para limpiar el código
        function cleanCodigo(value) {
            return value
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9\-]/g, '')
                .replace(/^\-+|\-+$/g, '');
        }
        
        // Convertir mientras escribe
        codigoInput.addEventListener('input', function(e) {
            const cleaned = cleanCodigo(this.value);
            if (this.value !== cleaned) {
                const start = this.selectionStart;
                this.value = cleaned;
                this.setSelectionRange(start, start);
            }
        });
        
        // Validar antes de enviar
        form.addEventListener('submit', function(e) {
            codigoInput.value = cleanCodigo(codigoInput.value);
            
            if (!codigoInput.value) {
                e.preventDefault();
                alert('El código del almacén no puede estar vacío');
                codigoInput.focus();
                return false;
            }
        });
    }
});
</script>
@endsection