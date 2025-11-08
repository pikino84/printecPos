@extends('layouts.app')
@section('title', 'Mis Almacenes')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5>Mis Almacenes</h5>
            @if($partner)
                <p class="text-muted mb-0">{{ $partner->name }}</p>
            @endif
        </div>
        <div class="col-md-4 text-right">
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <a href="{{ route('my-warehouses.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Agregar Almacén
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($warehouses->count() > 0)
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Apodo</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($warehouses as $warehouse)
                    <tr>
                        <td>
                            <code>{{ $warehouse->codigo }}</code>
                        </td>
                        <td>
                            <strong>{{ $warehouse->name }}</strong>
                        </td>
                        <td>
                            @if($warehouse->nickname)
                                <span class="badge bg-info">{{ $warehouse->nickname }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($warehouse->city)
                                <i class="feather icon-map-pin"></i> {{ $warehouse->city->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($warehouse->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $stockCount = $warehouse->stocks()->count();
                                $totalStock = $warehouse->stocks()->sum('stock');
                            @endphp
                            @if($stockCount > 0)
                                <span class="badge bg-primary" title="Variantes con stock">
                                    {{ $stockCount }} variantes
                                </span>
                                <br>
                                <small class="text-muted">{{ $totalStock }} unidades</small>
                            @else
                                <span class="text-muted">Sin stock</span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('my-warehouses.edit', $warehouse->id) }}" 
                                   class="btn btn-warning"
                                   title="Editar">
                                    <i class="feather icon-edit"></i>
                                </a>
                                
                                <button type="button"
                                        class="btn btn-danger"
                                        title="Eliminar"
                                        onclick="confirmDelete({{ $warehouse->id }}, '{{ $warehouse->name }}')">
                                    <i class="feather icon-trash-2"></i>
                                </button>
                                
                                <form id="delete-form-{{ $warehouse->id }}" 
                                      action="{{ route('my-warehouses.destroy', $warehouse->id) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mt-3">
    <div class="col-md-4">
        <div class="card bg-c-blue text-white">
            <div class="card-block">
                <h6 class="text-white">Total Almacenes</h6>
                <h2 class="text-white">{{ $warehouses->count() }}</h2>
                <p class="mb-0">Registrados</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-c-green text-white">
            <div class="card-block">
                <h6 class="text-white">Almacenes Activos</h6>
                <h2 class="text-white">{{ $warehouses->where('is_active', true)->count() }}</h2>
                <p class="mb-0">En operación</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-c-yellow text-white">
            <div class="card-block">
                <h6 class="text-white">Stock Total</h6>
                <h2 class="text-white">{{ $warehouses->sum(function($w) { return $w->stocks()->sum('stock'); }) }}</h2>
                <p class="mb-0">Unidades</p>
            </div>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body">
        <div class="text-center py-5">
            <i class="feather icon-package" style="font-size: 48px; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes almacenes registrados.</p>
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <p class="text-muted mb-3">
                <small>Necesitas al menos un almacén para poder crear productos propios.</small>
            </p>
            <a href="{{ route('my-warehouses.create') }}" class="btn btn-primary">
                <i class="feather icon-plus"></i> Crear primer almacén
            </a>
            @endif
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function confirmDelete(warehouseId, warehouseName) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar el almacén <strong>' + warehouseName + '</strong>?<br><br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + warehouseId).submit();
            }
        });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar el almacén "' + warehouseName + '"?\n\nEsta acción no se puede deshacer.',
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
                document.getElementById('delete-form-' + warehouseId).submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar el almacén "' + warehouseName + '"?')) {
            document.getElementById('delete-form-' + warehouseId).submit();
        }
    }
}
</script>
@endsection