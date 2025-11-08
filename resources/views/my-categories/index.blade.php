@extends('layouts.app')
@section('title', 'Mis Categorías')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Mis Categorías</h5>
            @if($partner)
                <p class="text-muted mb-0">{{ $partner->name }}</p>
            @else
                <p class="text-muted mb-0">Todas las categorías</p>
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

@if(auth()->user()->hasRole('Asociado Administrador|super admin'))
<!-- Formulario para crear categoría -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Crear Nueva Categoría</h5>
        <span class="text-muted">Completa el formulario para agregar una nueva categoría</span>
    </div>
    <div class="card-body">
        <form action="{{ route('my-categories.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-5">
                <input type="text" 
                       name="name" 
                       class="form-control @error('name') is-invalid @enderror" 
                       placeholder="Nombre de la categoría" 
                       value="{{ old('name') }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">
                    <i class="feather icon-plus"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Tabla de categorías -->
<div class="card">
    <div class="card-body">
        @if($categories->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        @if(!$partner)
                            <th>Partner</th>
                        @endif
                        <th>Estado</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>
                            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
                            <form method="POST" 
                                  action="{{ route('my-categories.update', $category->id) }}" 
                                  class="form-inline"
                                  id="form-name-{{ $category->id }}">
                                @csrf
                                @method('PUT')
                                <input type="text" 
                                       name="name" 
                                       class="form-control form-control-sm" 
                                       value="{{ $category->name }}" 
                                       style="min-width: 200px;"
                                       required>
                            </form>
                            @else
                                <strong>{{ $category->name }}</strong>
                            @endif
                        </td>
                        <td>
                            <code>{{ $category->slug }}</code>
                        </td>
                        @if(!$partner)
                            <td>
                                <span class="badge bg-info">{{ $category->partner->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $productCount = \App\Models\Product::where('product_category_id', $category->id)->count();
                            @endphp
                            @if($productCount > 0)
                                <span class="badge bg-primary">{{ $productCount }} productos</span>
                            @else
                                <span class="text-muted">Sin productos</span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
                            <div class="btn-group btn-group-sm">
                                <button type="submit" 
                                        form="form-name-{{ $category->id }}"
                                        class="btn btn-primary"
                                        title="Actualizar">
                                    <i class="feather icon-save"></i>
                                </button>
                                
                                <button type="button" 
                                        class="btn btn-danger"
                                        title="Eliminar"
                                        onclick="confirmDelete({{ $category->id }}, '{{ $category->name }}')">
                                    <i class="feather icon-trash-2"></i>
                                </button>
                                
                                <form id="delete-form-{{ $category->id }}" 
                                      action="{{ route('my-categories.destroy', $category->id) }}" 
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
        @else
        <div class="text-center py-5">
            <i class="feather icon-folder" style="font-size: 48px; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes categorías registradas.</p>
            @if(auth()->user()->hasRole('Asociado Administrador|super admin'))
            <p class="text-muted mb-3">
                <small>Crea categorías para organizar tus productos propios.</small>
            </p>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Estadísticas -->
@if($categories->count() > 0)
<div class="row mt-3">
    <div class="col-md-4">
        <div class="card bg-c-blue text-white">
            <div class="card-block">
                <h6 class="text-white">Total Categorías</h6>
                <h2 class="text-white">{{ $categories->count() }}</h2>
                <p class="mb-0">Registradas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-c-green text-white">
            <div class="card-block">
                <h6 class="text-white">Categorías Activas</h6>
                <h2 class="text-white">{{ $categories->where('is_active', true)->count() }}</h2>
                <p class="mb-0">Habilitadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-c-yellow text-white">
            <div class="card-block">
                <h6 class="text-white">Productos Totales</h6>
                @php
                    $totalProducts = $categories->sum(function($cat) {
                        return \App\Models\Product::where('product_category_id', $cat->id)->count();
                    });
                @endphp
                <h2 class="text-white">{{ $totalProducts }}</h2>
                <p class="mb-0">En todas las categorías</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function confirmDelete(categoryId, categoryName) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar la categoría <strong>' + categoryName + '</strong>?<br><br>Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + categoryId).submit();
            }
        });
    } else if (typeof swal !== 'undefined') {
        swal({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar la categoría "' + categoryName + '"?\n\nEsta acción no se puede deshacer.',
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
                document.getElementById('delete-form-' + categoryId).submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas eliminar la categoría "' + categoryName + '"?')) {
            document.getElementById('delete-form-' + categoryId).submit();
        }
    }
}
</script>
@endsection