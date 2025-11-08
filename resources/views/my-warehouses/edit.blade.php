@extends('layouts.app')
@section('title', 'Editar Almacén')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Editar Almacén</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Información del Almacén</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('my-warehouses.update', $warehouse->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Código (solo lectura) -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Código del Almacén
                    </label>
                    <input type="text" 
                           class="form-control" 
                           value="{{ $warehouse->codigo }}"
                           disabled>
                    <small class="form-text text-muted">
                        <i class="feather icon-lock"></i> El código no se puede modificar
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
                           value="{{ old('name', $warehouse->name) }}"
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
                           value="{{ old('nickname', $warehouse->nickname) }}"
                           placeholder="ej: Centro, Norte">
                    @error('nickname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                                    {{ old('city_id', $warehouse->city_id) == $city->id ? 'selected' : '' }}>
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
                               {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Almacén activo
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        <i class="feather icon-info"></i> Solo los almacenes activos aparecerán en el sistema
                    </small>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="alert alert-info">
                <strong><i class="feather icon-info"></i> Información:</strong>
                <ul class="mb-0 mt-2">
                    <li>Creado el: {{ $warehouse->created_at->format('d/m/Y H:i') }}</li>
                    <li>Última actualización: {{ $warehouse->updated_at->format('d/m/Y H:i') }}</li>
                </ul>
            </div>

            <hr class="my-3">

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Actualizar Almacén
                    </button>
                    <a href="{{ route('my-warehouses.index') }}" class="btn btn-secondary">
                        <i class="feather icon-x"></i> Cancelar
                    </a>
                    
                    <button type="button" 
                            class="btn btn-danger float-right" 
                            onclick="confirmDelete()">
                        <i class="feather icon-trash-2"></i> Eliminar Almacén
                    </button>
                </div>
            </div>
        </form>
        
        <form id="delete-form" 
              action="{{ route('my-warehouses.destroy', $warehouse->id) }}" 
              method="POST" 
              style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<!-- Estadísticas del almacén -->
<div class="card mt-3">
    <div class="card-header">
        <h5>Estadísticas del Almacén</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-c-blue text-white">
                    <div class="card-block">
                        <h6 class="text-white">Variantes con Stock</h6>
                        <h2 class="text-white">{{ $warehouse->stocks()->count() }}</h2>
                        <p class="mb-0">Productos diferentes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-c-green text-white">
                    <div class="card-block">
                        <h6 class="text-white">Stock Total</h6>
                        <h2 class="text-white">{{ $warehouse->stocks()->sum('stock') }}</h2>
                        <p class="mb-0">Unidades totales</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar este almacén?<br><br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar este almacén?\n\nEsta acción no se puede deshacer.',
            icon: 'warning',
            buttons: {
                cancel: {
                    text: 'Cancelar',
                    value: null,
                    visible: true,
                    closeModal: true,
                },
                confirm: {
                    text: 'Sí, eliminar',
                    value: true,
                    visible: true,
                    className: 'btn-danger',
                    closeModal: true
                }
            },
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                document.getElementById('delete-form').submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar este almacén?')) {
            document.getElementById('delete-form').submit();
        }
    }
}
</script>
@endsection