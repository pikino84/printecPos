@extends('layouts.app')

@section('title', $own_product->name)

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $own_product->name }}</h5>
                    <p class="m-b-0">Detalles del producto propio</p>
                </div>
            </div>
            <div class="col-md-4">
                <ul class="breadcrumb-title">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fa fa-home"></i></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('own-products.index') }}">Productos Propios</a>
                    </li>
                    <li class="breadcrumb-item"><a href="#!">{{ $own_product->name }}</a></li>
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
                            {{ $own_product->name }}
                            @if($own_product->featured)
                                <span class="badge badge-warning ml-2">Destacado</span>
                            @endif
                            @if($own_product->is_public && $own_product->partner_id == 1)
                                <span class="badge badge-info ml-1">Público</span>
                            @endif
                            <span class="badge badge-{{ $own_product->is_active ? 'success' : 'secondary' }} ml-1">
                                {{ $own_product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </h4>
                        @if($own_product->model_code)
                            <p class="text-muted mb-0">Modelo: <code>{{ $own_product->model_code }}</code></p>
                        @endif
                    </div>
                    <div class="col-md-4 text-right">
                        @can('update', $own_product)
                            <a href="{{ route('own-products.edit', $own_product) }}" class="btn btn-warning">
                                <i class="feather icon-edit"></i> Editar
                            </a>
                        @endcan
                        <a href="{{ route('own-products.index') }}" class="btn btn-secondary">
                            <i class="feather icon-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Información principal -->
                    <div class="col-lg-8">
                        <!-- Imagen principal -->
                        @if($own_product->main_image_url)
                            <div class="card">
                                <div class="card-header">
                                    <h5>Imagen Principal</h5>
                                </div>
                                <div class="card-block text-center">
                                    <img src="{{ $own_product->main_image_url }}" 
                                         alt="{{ $own_product->name }}"
                                         class="img-fluid rounded shadow-sm"
                                         style="max-height: 400px;">
                                </div>
                            </div>
                        @endif

                        <!-- Descripción -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Descripción</h5>
                            </div>
                            <div class="card-block">
                                @if($own_product->short_description)
                                    <p class="lead">{{ $own_product->short_description }}</p>
                                @endif
                                
                                @if($own_product->description)
                                    <div class="mt-3">
                                        {!! nl2br(e($own_product->description)) !!}
                                    </div>
                                @else
                                    <p class="text-muted">Sin descripción detallada</p>
                                @endif
                            </div>
                        </div>

                        <!-- Especificaciones -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Especificaciones</h5>
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    @if($own_product->material)
                                        <div class="col-md-6 mb-3">
                                            <strong>Material:</strong><br>
                                            <span class="text-muted">{{ $own_product->material }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($own_product->packing_type)
                                        <div class="col-md-6 mb-3">
                                            <strong>Tipo de Empaque:</strong><br>
                                            <span class="text-muted">{{ $own_product->packing_type }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($own_product->unit_package)
                                        <div class="col-md-6 mb-3">
                                            <strong>Unidad por Paquete:</strong><br>
                                            <span class="text-muted">{{ $own_product->unit_package }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($own_product->product_weight)
                                        <div class="col-md-6 mb-3">
                                            <strong>Peso:</strong><br>
                                            <span class="text-muted">{{ $own_product->product_weight }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($own_product->product_size)
                                        <div class="col-md-6 mb-3">
                                            <strong>Tamaño:</strong><br>
                                            <span class="text-muted">{{ $own_product->product_size }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($own_product->area_print)
                                        <div class="col-md-6 mb-3">
                                            <strong>Área de Impresión:</strong><br>
                                            <span class="text-muted">{{ $own_product->area_print }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if(!$own_product->material && !$own_product->packing_type && !$own_product->unit_package && !$own_product->product_weight && !$own_product->product_size && !$own_product->area_print)
                                    <p class="text-muted">No hay especificaciones registradas</p>
                                @endif
                            </div>
                        </div>

                        <!-- Variantes y Stock -->
                        @if($own_product->variants->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h5>Variantes y Stock</h5>
                                </div>
                                <div class="card-block">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>SKU</th>
                                                    <th>Color</th>
                                                    <th>Precio</th>
                                                    <th>Stock por Almacén</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($own_product->variants as $variant)
                                                <tr>
                                                    <td><code>{{ $variant->sku }}</code></td>
                                                    <td>
                                                        @if($variant->color_name)
                                                            <span class="badge badge-light">{{ $variant->color_name }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($variant->price)
                                                            <strong>${{ number_format($variant->price, 2) }}</strong>
                                                        @else
                                                            <span class="text-muted">${{ number_format($own_product->price, 2) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($variant->stocks->count() > 0)
                                                            @foreach($variant->stocks as $stock)
                                                                <small class="d-block">
                                                                    {{ $stock->warehouse->nickname ?? $stock->warehouse->name }}: 
                                                                    <span class="badge badge-{{ $stock->stock > 0 ? 'success' : 'danger' }} badge-sm">
                                                                        {{ $stock->stock }}
                                                                    </span>
                                                                </small>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">Sin stock</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $totalStock = $variant->stocks->sum('stock');
                                                        @endphp
                                                        <span class="badge badge-{{ $totalStock > 0 ? 'success' : 'danger' }}">
                                                            {{ $totalStock }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Panel lateral -->
                    <div class="col-lg-4">
                        <!-- Información comercial -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Información Comercial</h5>
                            </div>
                            <div class="card-block">
                                <div class="mb-3">
                                    <strong>Precio de Venta:</strong><br>
                                    <span class="h4 text-success">${{ number_format($own_product->price, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Categoría -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Categorización</h5>
                            </div>
                            <div class="card-block">
                                <div class="mb-3">
                                    <strong>Categoría:</strong><br>
                                    @if($own_product->productCategory)
                                        <span class="badge badge-primary">{{ $own_product->productCategory->name }}</span>
                                    @else
                                        <span class="text-muted">Sin categoría</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Información del producto -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Información del Producto</h5>
                            </div>
                            <div class="card-block">
                                <div class="mb-3">
                                    <strong>Propietario:</strong><br>
                                    <span class="badge badge-{{ $own_product->partner_id == auth()->user()->partner_id ? 'primary' : 'info' }}">
                                        {{ $own_product->partner->name }}
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Creado por:</strong><br>
                                    <span class="text-muted">{{ $own_product->creator->name }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Fecha de creación:</strong><br>
                                    <span class="text-muted">{{ $own_product->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                
                                @if($own_product->updated_at != $own_product->created_at)
                                    <div class="mb-3">
                                        <strong>Última actualización:</strong><br>
                                        <span class="text-muted">{{ $own_product->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif

                                @php
                                    $totalStock = $own_product->variants->sum(function($variant) {
                                        return $variant->stocks->sum('stock');
                                    });
                                @endphp
                                <div class="mb-3">
                                    <strong>Stock Total:</strong><br>
                                    <span class="badge badge-{{ $totalStock > 0 ? 'success' : 'danger' }}">
                                        {{ $totalStock }} unidades
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection