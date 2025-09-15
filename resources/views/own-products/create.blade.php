@extends('layouts.app')

@section('title', 'Nuevo Producto Propio')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">Nuevo Producto Propio</h5>
                    <p class="m-b-0">Crear un nuevo producto personalizado</p>
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
                    <li class="breadcrumb-item"><a href="#!">Nuevo</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
                
                <form action="{{ route('own-products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <!-- Información básica -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Información Básica</h5>
                                </div>
                                <div class="card-block">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="form-label">Código de Modelo</label>
                                            <input type="text" 
                                                   class="form-control @error('model_code') is-invalid @enderror" 
                                                   name="model_code" 
                                                   value="{{ old('model_code') }}">
                                            @error('model_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Descripción Corta</label>
                                        <input type="text" 
                                               class="form-control @error('short_description') is-invalid @enderror" 
                                               name="short_description" 
                                               value="{{ old('short_description') }}"
                                               maxlength="500">
                                        @error('short_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Descripción Completa</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  name="description" 
                                                  rows="4">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Especificaciones -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Especificaciones</h5>
                                </div>
                                <div class="card-block">
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Material</label>
                                            <input type="text" 
                                                   class="form-control @error('material') is-invalid @enderror" 
                                                   name="material" 
                                                   value="{{ old('material') }}">
                                            @error('material')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Tipo de Empaque</label>
                                            <input type="text" 
                                                   class="form-control @error('packing_type') is-invalid @enderror" 
                                                   name="packing_type" 
                                                   value="{{ old('packing_type') }}">
                                            @error('packing_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Unidad por Paquete</label>
                                            <input type="text" 
                                                   class="form-control @error('unit_package') is-invalid @enderror" 
                                                   name="unit_package" 
                                                   value="{{ old('unit_package') }}">
                                            @error('unit_package')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Peso del Producto</label>
                                            <input type="text" 
                                                   class="form-control @error('product_weight') is-invalid @enderror" 
                                                   name="product_weight" 
                                                   value="{{ old('product_weight') }}">
                                            @error('product_weight')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Tamaño del Producto</label>
                                            <input type="text" 
                                                   class="form-control @error('product_size') is-invalid @enderror" 
                                                   name="product_size" 
                                                   value="{{ old('product_size') }}"
                                                   placeholder="L x W x H">
                                            @error('product_size')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Área de Impresión</label>
                                            <input type="text" 
                                                   class="form-control @error('area_print') is-invalid @enderror" 
                                                   name="area_print" 
                                                   value="{{ old('area_print') }}"
                                                   placeholder="Ej: 10x10cm">
                                            @error('area_print')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Imagen -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Imagen Principal</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group">
                                        <label class="form-label">Imagen Principal</label>
                                        <input type="file" 
                                               class="form-control-file @error('main_image') is-invalid @enderror" 
                                               name="main_image" 
                                               accept="image/*">
                                        @error('main_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Máximo 5MB. Formatos: JPG, PNG, WebP</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel lateral -->
                        <div class="col-lg-4">
                            <!-- Precio -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Precio</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group">
                                        <label class="form-label">Precio de Venta <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" 
                                                   class="form-control @error('price') is-invalid @enderror" 
                                                   name="price" 
                                                   value="{{ old('price') }}"
                                                   step="0.01"
                                                   min="0"
                                                   required>
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Categoría -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Categoría</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group">
                                        <label class="form-label">Categoría</label>
                                        <select class="form-control @error('product_category_id') is-invalid @enderror" 
                                                name="product_category_id">
                                            <option value="">Sin categoría</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('product_category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Stock inicial -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Inventario Inicial</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group">
                                        <label class="form-label">SKU de la Variante Principal</label>
                                        <input type="text" 
                                               class="form-control @error('sku') is-invalid @enderror" 
                                               name="sku" 
                                               value="{{ old('sku') }}"
                                               style="text-transform: uppercase">
                                        @error('sku')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Si se deja vacío, se generará automáticamente</small>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Almacén</label>
                                        <select class="form-control @error('warehouse_id') is-invalid @enderror" 
                                                name="warehouse_id">
                                            <option value="">Seleccionar almacén</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" 
                                                        {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                    {{ $warehouse->name }} ({{ $warehouse->nickname }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('warehouse_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Stock Inicial</label>
                                        <input type="number" 
                                               class="form-control @error('initial_stock') is-invalid @enderror" 
                                               name="initial_stock" 
                                               value="{{ old('initial_stock', 0) }}"
                                               min="0">
                                        @error('initial_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Estado y Visibilidad</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   name="is_active" 
                                                   id="is_active" 
                                                   value="1" 
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Producto activo
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   name="featured" 
                                                   id="featured" 
                                                   value="1" 
                                                   {{ old('featured') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="featured">
                                                Producto destacado
                                            </label>
                                        </div>
                                    </div>

                                    @if(auth()->user()->partner_id == 1)
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="is_public" 
                                                       id="is_public" 
                                                       value="1" 
                                                       {{ old('is_public') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_public">
                                                    Visible para asociados
                                                </label>
                                                <small class="form-text text-muted">Solo Printec puede hacer productos públicos</small>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="card">
                                <div class="card-block">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="feather icon-save"></i> Crear Producto
                                    </button>
                                    <a href="{{ route('own-products.index') }}" class="btn btn-secondary btn-block">
                                        <i class="feather icon-x"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection