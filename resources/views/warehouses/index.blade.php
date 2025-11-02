@extends('layouts.app')

@section('title', 'Almacenes')

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-12">
            <h5>Gestión de Almacenes</h5>
            @if (session('success'))
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    swal("¡Éxito!", "{{ session('success') }}", "success");
                });
            </script>
            @elseif (session('error'))
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    swal("¡Error!", "{{ session('error') }}", "error");
                });
            </script>
            @endif
            <a href="{{ route('warehouses.create') }}" class="btn btn-primary float-right">
                <i class="feather icon-plus"></i> Nuevo Almacén
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-block table-border-style">
        <div class="table-responsive">
            @if($warehouses->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="feather icon-info" style="font-size: 48px;"></i>
                    <h5 class="mt-3">No hay almacenes registrados</h5>
                    <p>Comienza creando tu primer almacén</p>
                    <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
                        <i class="feather icon-plus"></i> Crear Almacén
                    </a>
                </div>
            @else
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Nickname</th>
                            <th>Ciudad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($warehouses as $warehouse)
                            <tr>      
                                <td>
                                    <strong>{{ $warehouse->partner->name ?? 'Sin asignar' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $warehouse->partner->type ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <code>{{ $warehouse->codigo }}</code>
                                </td>
                                <td>{{ $warehouse->name }}</td>
                                <td>
                                    @if($warehouse->nickname)
                                        <span class="badge badge-info">{{ $warehouse->nickname }}</span>
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
                                        <span class="badge badge-success">
                                            <i class="feather icon-check"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="feather icon-x"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Editar --}}
                                    <a href="{{ route('warehouses.edit', $warehouse->id) }}" 
                                       class="btn btn-sm btn-warning">
                                        <i class="feather icon-edit"></i> Editar
                                    </a>

                                    {{-- Eliminar --}}
                                    <form action="{{ route('warehouses.destroy', $warehouse->id) }}" 
                                          method="POST" 
                                          style="display:inline;" 
                                          id="delete-form-{{ $warehouse->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete({{ $warehouse->id }}, '{{ $warehouse->name }}')">
                                            <i class="feather icon-trash-2"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Estadísticas --}}
@if(!$warehouses->isEmpty())
<div class="row">
    <div class="col-md-4">
        <div class="card bg-c-blue text-white">
            <div class="card-block">
                <h6 class="text-white">Total Almacenes</h6>
                <h2 class="text-white">{{ $warehouses->count() }}</h2>
                <p class="mb-0">Registrados en el sistema</p>
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
                <h6 class="text-white">Partners con Almacenes</h6>
                <h2 class="text-white">{{ $warehouses->unique('partner_id')->count() }}</h2>
                <p class="mb-0">Partners únicos</p>
            </div>
        </div>
    </div>
</div>
@endif

<script>
function confirmDelete(warehouseId, warehouseName) {
    swal({
        title: "¿Estás seguro?",
        text: "¿Deseas eliminar el almacén '" + warehouseName + "'? Esta acción no se puede deshacer y puede afectar los productos asociados.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            document.getElementById('delete-form-' + warehouseId).submit();
        }
    });
}
</script>
@endsection