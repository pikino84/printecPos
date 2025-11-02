@extends('layouts.app')

@section('title', 'Crear Almacén')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">Crear Nuevo Almacén</h5>
                    <p class="m-b-0">Registra un nuevo almacén para tu partner</p>
                </div>
            </div>
            <div class="col-md-4">
                <ul class="breadcrumb-title">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('warehouses.index') }}">Almacenes</a>
                    </li>
                    <li class="breadcrumb-item"><a href="#!">Crear</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Información del Almacén</h5>
                            </div>
                            <div class="card-block">
                                <form action="{{ route('warehouses.store') }}" method="POST" id="warehouseForm">
                                    @csrf

                                    {{-- Partner --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Partner/Proveedor <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('partner_id') is-invalid @enderror" 
                                                name="partner_id" 
                                                id="partner_id"
                                                required>
                                            <option value="">Seleccionar partner</option>
                                            @foreach($partners as $partner)
                                                <option value="{{ $partner->id }}" 
                                                        {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                                    {{ $partner->name }} ({{ $partner->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('partner_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Selecciona el partner dueño del almacén
                                        </small>
                                    </div>

                                    {{-- Código --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Código del Almacén <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('codigo') is-invalid @enderror" 
                                               name="codigo" 
                                               id="codigo"
                                               value="{{ old('codigo') }}"
                                               placeholder="Ej: cdmx-centro, gdl-zapopan"
                                               required>
                                        @error('codigo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Código único interno (solo minúsculas, números y guiones)
                                        </small>
                                    </div>

                                    {{-- Nombre --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Nombre del Almacén <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               name="name" 
                                               value="{{ old('name') }}"
                                               placeholder="Ej: Almacén Central CDMX"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Nombre completo del almacén
                                        </small>
                                    </div>

                                    {{-- Nickname --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Apodo/Alias (Opcional)
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('nickname') is-invalid @enderror" 
                                               name="nickname" 
                                               value="{{ old('nickname') }}"
                                               placeholder="Ej: Centro, Norte, Sur">
                                        @error('nickname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Nombre corto para mostrar públicamente
                                        </small>
                                    </div>

                                    {{-- Ciudad --}}
                                    <div class="form-group">
                                        <label class="form-label">
                                            Ciudad
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
                                        <small class="form-text text-muted">
                                            <i class="feather icon-info"></i> Ciudad donde se ubica el almacén
                                        </small>
                                    </div>

                                    {{-- Estado Activo --}}
                                    <div class="form-group">
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

                                    {{-- Botones --}}
                                    <div class="form-group mb-0">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather icon-save"></i> Crear Almacén
                                        </button>
                                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                                            <i class="feather icon-x"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const codigoInput = document.getElementById('codigo');
    const form = document.getElementById('warehouseForm');
    
    if (codigoInput) {
        // Función para limpiar el código
        function cleanCodigo(value) {
            return value
                .toLowerCase()                    // Convertir a minúsculas
                .replace(/\s+/g, '-')            // Espacios a guiones
                .replace(/[^a-z0-9\-]/g, '')     // Solo permitir a-z, 0-9 y guiones
                .replace(/\-+/g, '-')            // Múltiples guiones a uno solo
                .replace(/^\-+|\-+$/g, '');      // Eliminar guiones al inicio y final
        }
        
        // Convertir mientras escribe
        codigoInput.addEventListener('input', function(e) {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const cleaned = cleanCodigo(this.value);
            
            if (this.value !== cleaned) {
                this.value = cleaned;
                // Mantener posición del cursor
                this.setSelectionRange(start, end);
            }
        });
        
        // Validar al perder el foco
        codigoInput.addEventListener('blur', function() {
            this.value = cleanCodigo(this.value);
        });
        
        // Limpiar antes de pegar
        codigoInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = cleanCodigo(pastedText);
            
            // Insertar el texto limpio
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const currentValue = this.value;
            this.value = currentValue.substring(0, start) + cleaned + currentValue.substring(end);
            
            // Posicionar cursor después del texto pegado
            const newPosition = start + cleaned.length;
            this.setSelectionRange(newPosition, newPosition);
        });
    }
    
    // Validación final antes de enviar
    if (form) {
        form.addEventListener('submit', function(e) {
            if (codigoInput) {
                // Última limpieza antes de enviar
                codigoInput.value = codigoInput.value
                    .toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9\-]/g, '')
                    .replace(/\-+/g, '-')
                    .replace(/^\-+|\-+$/g, '');
                
                // Validar que no esté vacío después de limpiar
                if (!codigoInput.value) {
                    e.preventDefault();
                    alert('El código del almacén no puede estar vacío');
                    codigoInput.focus();
                    return false;
                }
            }
        });
    }
});
</script>
@endpush