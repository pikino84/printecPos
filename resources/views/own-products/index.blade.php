@extends('layouts.app')

@section('title', 'Productos Propios')

@section('content')
{{-- show error --}}
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">Productos Propios</h5>
                    <p class="m-b-0">Gestiona tus productos personalizados</p>
                </div>
            </div>
            <div class="col-md-4">
                <ul class="breadcrumb-title">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a>
                    </li>
                    <li class="breadcrumb-item"><a href="#!">Productos Propios</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
                
                <!-- Header con acciones -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h4 class="mb-0">
                            <i class="feather icon-package"></i> Productos Propios
                            <small class="text-muted">({{ $products->total() }} productos)</small>
                        </h4>
                    </div>
                    <div class="col-md-4 text-right">
                        @can('manage-own-products')
                            <a href="{{ route('own-products.create') }}" class="btn btn-primary">
                                <i class="feather icon-plus"></i> Nuevo Producto
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card">
                    <div class="card-header">
                        <h5>Filtros</h5>
                        <div class="card-header-right">
                            <ul class="list-unstyled card-option">
                                <li><i class="feather icon-chevron-down f-16"></i></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-block">
                        <form method="GET" action="{{ route('own-products.index') }}" class="row">
                            <div class="col-md-3 form-group">
                                <label class="form-label">Buscar</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Nombre, modelo, SKU...">
                            </div>
                            
                            <div class="col-md-3 form-group">
                                <label class="form-label">Categoría</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Todas las categorías</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 form-group">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                    <option value="featured" {{ request('status') == 'featured' ? 'selected' : '' }}>Destacados</option>
                                </select>
                            </div>

                            <div class="col-md-2 form-group">
                                <label class="form-label">Propietario</label>
                                <select name="owner" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="own" {{ request('owner') == 'own' ? 'selected' : '' }}>Mis productos</option>
                                    @if(auth()->user()->partner_id != 1)
                                        <option value="printec" {{ request('owner') == 'printec' ? 'selected' : '' }}>Productos Printec</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-2 form-group">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="feather icon-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('own-products.index') }}" class="btn btn-secondary">
                                        <i class="feather icon-x"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="card">
                    <div class="card-block px-0 py-3">
                        @if($products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Categoría</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                            <th>Estado</th>
                                            <th>Propietario</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->main_image_url)
                                                        <img src="{{ $product->main_image_url }}" 
                                                             alt="{{ $product->name }}"
                                                             class="img-thumbnail mr-3"
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    @else
                                                        <div class="mr-3 d-flex align-items-center justify-content-center bg-light" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="feather icon-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $product->name }}</strong>
                                                        @if($product->model_code)
                                                            <br><small class="text-muted">{{ $product->model_code }}</small>
                                                        @endif
                                                        @if($product->featured)
                                                            <span class="badge badge-warning badge-sm ml-1">Destacado</span>
                                                        @endif
                                                        @if($product->is_public && $product->partner_id == 1)
                                                            <span class="badge badge-info badge-sm ml-1">Público</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($product->productCategory)
                                                    <span>{{ $product->productCategory->name }}</span>
                                                @else
                                                    <span class="text-muted">Sin categoría</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-success">${{ number_format($product->price, 2) }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $totalStock = $product->variants->sum(function($variant) {
                                                        return $variant->stocks->sum('stock');
                                                    });
                                                @endphp
                                                <span class="badge badge-{{ $totalStock > 0 ? 'success' : 'danger' }}">
                                                    {{ $totalStock }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">
                                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($product->partner_id == auth()->user()->partner_id)
                                                    <span class="badge badge-primary">Propio</span>
                                                @elseif($product->partner_id == 1)
                                                    <span class="badge badge-info">Printec</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ $product->partner->name }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('own-products.show', $product) }}" 
                                                       class="btn btn-outline-primary"
                                                       title="Ver detalles">
                                                        <i class="feather icon-eye"></i>
                                                    </a>
                                                    @can('update', $product)
                                                        <a href="{{ route('own-products.edit', $product) }}" 
                                                           class="btn btn-outline-warning"
                                                           title="Editar">
                                                            <i class="feather icon-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete', $product)
                                                        <button type="button" 
                                                                class="btn btn-outline-danger"
                                                                onclick="confirmDelete({{ $product->id }})"
                                                                title="Eliminar">
                                                            <i class="feather icon-trash-2"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <div class="d-flex justify-content-between align-items-center mt-3 px-3">
                                <div class="text-muted">
                                    Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} 
                                    de {{ $products->total() }} productos
                                </div>
                                {{ $products->withQueryString()->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="feather icon-package f-40 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay productos propios</h5>
                                <p class="text-muted">Comienza creando tu primer producto personalizado</p>
                                @can('manage-own-products')
                                    <a href="{{ route('own-products.create') }}" class="btn btn-primary">
                                        <i class="feather icon-plus"></i> Crear Primer Producto
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script>
function confirmDelete(productId) {
    swal({
        title: "¿Estás seguro?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        buttons: ["Cancelar", "Eliminar"],
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            // Crear y enviar formulario dinámicamente
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/own-products/${productId}`;
            form.style.display = 'none';
            
            // Token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Method DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endsection