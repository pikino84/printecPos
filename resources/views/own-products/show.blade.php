@extends('layouts.app')

@section('title', $ownProduct->name)

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $ownProduct->name }}</h5>
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
                    <li class="breadcrumb-item"><a href="#!">{{ $ownProduct->name }}</a></li>
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
                            {{ $ownProduct->name }}
                            @if($ownProduct->featured)
                                <span class="badge badge-warning ml-2">Destacado</span>
                            @endif
                            @if($ownProduct->is_public && $ownProduct->partner_id == 1)
                                <span class="badge badge-info ml-1">Público</span>
                            @endif
                            <span class="badge badge-{{ $ownProduct->is_active ? 'success' : 'secondary' }} ml-1">
                                {{ $ownProduct->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </h4>
                        @if($ownProduct->model_code)
                            <p class="text-muted mb-0">Modelo: <code>{{ $ownProduct->model_code }}</code></p>
                        @endif
                    </div>
                    <div class="col-md-4 text-right">
                        @can('update', $ownProduct)
                            <a href="{{ route('own-products.edit', $ownProduct) }}" class="btn btn-warning">
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
                        @if($ownProduct->main_image_url)
                            <div class="card">
                                <div class="card-header">
                                    <h5>Imagen Principal</h5>
                                </div>
                                <div class="card-block text-center">
                                    <img src="{{ $ownProduct->main_image_url }}" 
                                         alt="{{ $ownProduct->name }}"
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
                                @if($ownProduct->short_description)
                                    <p class="lead">{{ $ownProduct->short_description }}</p>
                                @endif
                                
                                @if($ownProduct->description)
                                    <div class="mt-3">
                                        {!! nl2br(e($ownProduct->description)) !!}
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
                                    @if($ownProduct->material)
                                        <div class="col-md-6 mb-3">
                                            <strong>Material:</strong><br>
                                            <span class="text-muted">{{ $ownProduct->material }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($ownProduct->packing_type)
                                        <div class="col-md-6 mb-3">
                                            <strong>Tipo de Empaque:</strong><br>
                                            <span class="text-muted">{{ $ownProduct->packing_type }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($ownProduct->unit_package)
                                        <div class="col-md-6 mb-3">
                                            <strong>Unidad por Paquete:</strong><br>
                                            <span class="text-muted">{{ $ownProduct->unit_package }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($ownProduct->product_weight)
                                        <div class="col-md-6 mb-3">
                                            <strong>Peso:</strong><br>
                                            <span class="text-muted">{{ $ownProduct->product_weight }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($ownProduct->product_size)
                                        <div class="col-md-6 mb-3">
                                            <strong>Tamaño:</strong><br>
                                            <span class="text-muted">{{ $ownProduct->product_size }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($ownProduct->area_print)
                                        <div class="col-md-6 mb-3">
                                            <strong>Área de Impresión:</strong><br>
                                            <span class="text-muted">{{ $ownProduct->area_print }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if(!$ownProduct->material && !$ownProduct->packing_type && !$ownProduct->unit_package && !$ownProduct->product_weight && !$ownProduct->product_size && !$ownProduct->area_print)
                                    <p class="text-muted">No hay especificaciones registradas</p>
                                @endif
                            </div>
                        </div>

                        <!-- Variantes y Stock -->
                        @if($ownProduct->variants->count() > 0)
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
                                                @foreach($ownProduct->variants as $variant)
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
                                                            <span class="text-muted">${{ number_format($ownProduct->price, 2) }}</span>
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
                                    <span class="h4 text-success">${{ number_format($ownProduct->price, 2) }}</span>
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
                                    @if($ownProduct->productCategory)
                                        <span class="badge badge-primary">{{ $ownProduct->productCategory->name }}</span>
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
                                    <span class="badge badge-{{ $ownProduct->partner_id == auth()->user()->partner_id ? 'primary' : 'info' }}">
                                        {{ $ownProduct->partner->name }}
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Creado por:</strong><br>
                                    <span class="text-muted">{{ $ownProduct->creator->name }}</span>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Fecha de creación:</strong><br>
                                    <span class="text-muted">{{ $ownProduct->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                
                                @if($ownProduct->updated_at != $ownProduct->created_at)
                                    <div class="mb-3">
                                        <strong>Última actualización:</strong><br>
                                        <span class="text-muted">{{ $ownProduct->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif

                                @php
                                    $totalStock = $ownProduct->variants->sum(function($variant) {
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