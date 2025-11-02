@extends('layouts.app')

@section('title', 'Editar: ' . $ownProduct->name)

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">Editar Producto Propio</h5>
                    <p class="m-b-0">{{ $ownProduct->name }}</p>
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
                    <li class="breadcrumb-item">
                        <a href="{{ route('own-products.show', $ownProduct) }}">{{ $ownProduct->name }}</a>
                    </li>
                    <li class="breadcrumb-item"><a href="#!">Editar</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
                <form action="{{ route('own-products.update', $ownProduct) }}" method="POST" enctype="multipart/form-data" id="productForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <!-- Información básica -->
                        <div class="col-lg-8">
                        {{-- 🆕 CARD: PROVEEDOR--}}
                        <div class="card">
                            <div class="card-header">
                                <h5>Proveedor</h5>
                            </div>
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">
                                            Proveedor <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control @error('partner_id') is-invalid @enderror" 
                                                name="partner_id" 
                                                id="partner_id"
                                                required>
                                            <option value="">Seleccionar proveedor</option>
                                            @foreach($partners as $partner)
                                                <option value="{{ $partner->id }}" 
                                                        data-type="{{ $partner->type }}"
                                                        {{ old('partner_id', $ownProduct->partner_id) == $partner->id ? 'selected' : '' }}>
                                                    {{ $partner->name }} ({{ $partner->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('partner_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted" id="partner-info">
                                            <i class="feather icon-info"></i> Proveedor del producto
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                                value="{{ old('name', $ownProduct->name) }}" 
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
                                                value="{{ old('model_code', $ownProduct->model_code) }}">
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
                                            value="{{ old('short_description', $ownProduct->short_description) }}"
                                            maxlength="500">
                                    @error('short_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Descripción Completa</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                                name="description" 
                                                rows="4">{{ old('description', $ownProduct->description) }}</textarea>
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
                                                value="{{ old('material', $ownProduct->material) }}">
                                        @error('material')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Tipo de Empaque</label>
                                        <input type="text" 
                                                class="form-control @error('packing_type') is-invalid @enderror" 
                                                name="packing_type" 
                                                value="{{ old('packing_type', $ownProduct->packing_type) }}">
                                        @error('packing_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Unidad por Paquete</label>
                                        <input type="text" 
                                                class="form-control @error('unit_package') is-invalid @enderror" 
                                                name="unit_package" 
                                                value="{{ old('unit_package', $ownProduct->unit_package) }}">
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
                                                value="{{ old('product_weight', $ownProduct->product_weight) }}">
                                        @error('product_weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Tamaño del Producto</label>
                                        <input type="text" 
                                                class="form-control @error('product_size') is-invalid @enderror" 
                                                name="product_size" 
                                                value="{{ old('product_size', $ownProduct->product_size) }}"
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
                                                value="{{ old('area_print', $ownProduct->area_print) }}"
                                                placeholder="Ej: 10x10cm">
                                        @error('area_print')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Imagen actual y nueva -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Imagen Principal</h5>
                            </div>
                            <div class="card-block">
                                @if($ownProduct->main_image_url)
                                    <div class="mb-3">
                                        <label class="form-label">Imagen Actual:</label><br>
                                        <img src="{{ $ownProduct->main_image_url }}" 
                                                alt="Imagen actual"
                                                class="img-thumbnail"
                                                style="max-height: 200px;">
                                    </div>
                                @endif
                                
                                <div class="form-group">
                                    <label class="form-label">Nueva Imagen Principal</label>
                                    <input type="file" 
                                            class="form-control-file @error('main_image') is-invalid @enderror" 
                                            name="main_image" 
                                            accept="image/*">
                                    @error('main_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Deja vacío para mantener la imagen actual</small>
                                </div>
                            </div>
                        </div>

                        <!--Gestión de Variantes -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Gestión de Variantes</h5>
                                <button type="button" class="btn btn-sm btn-primary float-right" id="addVariant">
                                    <i class="feather icon-plus"></i> Agregar Variante
                                </button>
                            </div>
                            <div class="card-block">
                                <div id="variants-container">
                                    @forelse($ownProduct->variants as $index => $variant)
                                        <div class="variant-item border rounded p-3 mb-3">
                                            <input type="hidden" name="variants[{{$index}}][id]" value="{{$variant->id}}">
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">Variante {{$index + 1}}</h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-variant">
                                                    <i class="feather icon-trash-2"></i> Eliminar
                                                </button>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label">SKU de la Variante <span class="text-danger">*</span></label>
                                                    <input type="text" 
                                                        name="variants[{{$index}}][sku]" 
                                                        value="{{old('variants.'.$index.'.sku', $variant->sku)}}"
                                                        class="form-control @error('variants.'.$index.'.sku') is-invalid @enderror" 
                                                        required
                                                        style="text-transform: uppercase">
                                                    @error('variants.'.$index.'.sku')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label">Nombre del Color/Variante</label>
                                                    <input type="text" 
                                                        name="variants[{{$index}}][color_name]" 
                                                        value="{{old('variants.'.$index.'.color_name', $variant->color_name)}}"
                                                        class="form-control @error('variants.'.$index.'.color_name') is-invalid @enderror" 
                                                        placeholder="Ej: Azul, Negro, Rojo">
                                                    @error('variants.'.$index.'.color_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label">Precio Específico</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">$</span>
                                                        </div>
                                                        <input type="number" 
                                                            name="variants[{{$index}}][price]" 
                                                            value="{{old('variants.'.$index.'.price', $variant->price)}}"
                                                            class="form-control @error('variants.'.$index.'.price') is-invalid @enderror" 
                                                            step="0.01" 
                                                            min="0"
                                                            placeholder="Deja vacío para usar precio base"
                                                            required>
                                                    </div>
                                                    <small class="form-text text-muted">Si no se especifica, usa el precio base del producto</small>
                                                    @error('variants.'.$index.'.price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label class="form-label">Imagen de la Variante</label>
                                                    @if($variant->image)
                                                        <div class="mb-2">
                                                            <img src="{{ Storage::url($variant->image) }}" 
                                                                alt="Imagen actual" 
                                                                class="img-thumbnail"
                                                                style="max-height: 80px;"
                                                                id="variant-preview-{{$index}}">
                                                        </div>
                                                    @else
                                                        <img id="variant-preview-{{$index}}" 
                                                            style="max-height: 80px; display: none;" 
                                                            class="img-thumbnail mb-2">
                                                    @endif
                                                    <input type="file" 
                                                        name="variants[{{$index}}][image]" 
                                                        class="form-control-file @error('variants.'.$index.'.image') is-invalid @enderror" 
                                                        accept="image/*"
                                                        onchange="previewVariantImage(this, {{$index}})">
                                                    <small class="form-text text-muted">Deja vacío para mantener la imagen actual</small>
                                                    @error('variants.'.$index.'.image')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-12">
                                                    <label class="form-label">Stock por Almacén</label>
                                                    <div class="row">
                                                        @foreach($warehouses as $warehouse)
                                                            @php
                                                                $currentStock = $variant->stocks->where('warehouse_id', $warehouse->id)->first();
                                                            @endphp
                                                            <div class="col-md-6 form-group">
                                                                <label class="form-label small">{{$warehouse->name}} @if($warehouse->nickname)({{$warehouse->nickname}})@endif</label>
                                                                <input type="number" 
                                                                    name="variants[{{$index}}][stocks][{{$warehouse->id}}]" 
                                                                    value="{{old('variants.'.$index.'.stocks.'.$warehouse->id, $currentStock ? $currentStock->stock : 0)}}"
                                                                    class="form-control form-control-sm" 
                                                                    min="0"
                                                                    placeholder="0">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-4" id="no-variants-message">
                                            <i class="feather icon-layers f-40 mb-3"></i>
                                            <p>No hay variantes configuradas. Haz clic en "Agregar Variante" para comenzar.</p>
                                        </div>
                                    @endforelse
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
                                                value="{{ old('price', $ownProduct->price) }}"
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
                                                    {{ old('product_category_id', $ownProduct->product_category_id) == $category->id ? 'selected' : '' }}>
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
                                                {{ old('is_active', $ownProduct->is_active) ? 'checked' : '' }}>
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
                                                {{ old('featured', $ownProduct->featured) ? 'checked' : '' }}>
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
                                                    {{ old('is_public', $ownProduct->is_public) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_public">
                                                Visible para asociados
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card">
                            <div class="card-block">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="feather icon-save"></i> Actualizar Producto
                                </button>
                                <a href="{{ route('own-products.show', $ownProduct) }}" class="btn btn-secondary btn-block">
                                    <i class="feather icon-x"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let variantCounter = document.querySelectorAll('.variant-item').length;

    const partnerSelect = document.getElementById('partner_id');
    const warehouseGroup = document.getElementById('warehouse-group');
    const warehouseSelect = document.getElementById('warehouse_id');
    const warehouseRequired = document.getElementById('warehouse-required');
    const warehouseHelp = document.getElementById('warehouse-help');
    const partnerInfo = document.getElementById('partner-info');

    partnerSelect.addEventListener('change', function() {
        const partnerId = this.value;
        
        // Limpiar
        warehouseSelect.innerHTML = '<option value="">Seleccionar almacén</option>';
        warehouseGroup.style.display = 'none';
        warehouseSelect.removeAttribute('required');
        partnerInfo.innerHTML = '<i class="feather icon-info"></i> Proveedor del producto';

        if (!partnerId) {
            return;
        }

        // Loading
        partnerInfo.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando...';

        // AJAX
        fetch(`/api/partners/${partnerId}/warehouses`)
            .then(response => {
                if (!response.ok) throw new Error('Error');
                return response.json();
            })
            .then(data => {

                partnerInfo.innerHTML = `<strong>${data.type_label}</strong> - ${data.type_description}`;

                if (data.requires_warehouse) {
                    // Proveedor o Mixto: REQUIERE almacén
                    warehouseGroup.style.display = 'block';
                    warehouseSelect.setAttribute('required', 'required');
                    warehouseRequired.style.display = 'inline';
                    
                    if (data.warehouses && data.warehouses.length > 0) {
                        data.warehouses.forEach(warehouse => {
                            const option = document.createElement('option');
                            option.value = warehouse.id;
                            
                            let text = warehouse.name;
                            if (warehouse.city) text += ` (${warehouse.city})`;
                            option.textContent = text;
                            
                            // Seleccionar el warehouse actual
                            if (warehouse.id == currentWarehouseId) {
                                option.selected = true;
                            }
                            
                            warehouseSelect.appendChild(option);
                        });
                        
                        warehouseHelp.innerHTML = '<i class="feather icon-check text-success"></i> Almacén del producto.';
                    } else {
                        warehouseHelp.innerHTML = '<strong class="text-warning"><i class="feather icon-alert-triangle"></i> No hay almacenes disponibles.</strong>';
                    }
                } else {
                    // Asociado: NO requiere almacén
                    warehouseGroup.style.display = 'none';
                    warehouseSelect.removeAttribute('required');
                    partnerInfo.innerHTML += '<br><span class="text-success"><i class="feather icon-check"></i> No requiere almacén.</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                partnerInfo.innerHTML = '<span class="text-danger"><i class="feather icon-x"></i> Error al cargar</span>';
            });
    });

    // Trigger para cargar almacenes del partner actual
    if (partnerSelect.value) {
        partnerSelect.dispatchEvent(new Event('change'));
    }
    
    // Warehouse ID actual del producto
    const currentWarehouseId = "{{ old('warehouse_id', $ownProduct->warehouse_id ?? '') }}";
    
    // Agregar nueva variante CON DEBUGGING
    const addVariantBtn = document.getElementById('addVariant');

    if (!addVariantBtn) {
        console.error('❌ Button #addVariant NOT FOUND!');
        alert('Error: Botón no encontrado. Contacta soporte.');
    } else {
        
        addVariantBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Evitar comportamiento por defecto
            
            console.log('🔘 Add variant clicked!');
            console.log('📊 Current variantCounter:', variantCounter);
            
            const container = document.getElementById('variants-container');
            
            if (!container) {
                console.error('❌ variants-container NOT FOUND!');
                alert('Error: Container no encontrado');
                return;
            }
            
            const noVariantsMessage = document.getElementById('no-variants-message');
            if (noVariantsMessage) {
                console.log('🗑️ Removing no-variants message');
                noVariantsMessage.remove();
            }
            
            try {
                console.log('🏗️ Creating variant HTML for index:', variantCounter);
                const newVariant = createVariantHTML(variantCounter);
                
                console.log('✅ HTML created, adding to DOM...');
                container.insertAdjacentHTML('beforeend', newVariant);
                
                variantCounter++;
                console.log('✅ Variant added! New counter:', variantCounter);
                
            } catch (error) {
                console.error('❌ ERROR:', error);
                alert('Error al crear variante: ' + error.message);
            }
        });
    }
    
    // Eliminar variante
    document.getElementById('variants-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant') || e.target.closest('.remove-variant')) {
            e.target.closest('.variant-item').remove();
            
            // Si no quedan variantes, mostrar mensaje
            if (document.querySelectorAll('.variant-item').length === 0) {
                document.getElementById('variants-container').innerHTML = 
                    `<div class="text-center text-muted py-4" id="no-variants-message">
                        <i class="feather icon-layers f-40 mb-3"></i>
                        <p>No hay variantes configuradas. Haz clic en "Agregar Variante" para comenzar.</p>
                    </div>`;
            }
        }
    });
    
    // Generar SKU automático
    document.getElementById('variants-container').addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('[color_name]')) {
            const variantItem = e.target.closest('.variant-item');
            const skuInput = variantItem.querySelector('input[name*="[sku]"]');
            const baseCode = document.querySelector('input[name="model_code"]').value || 'PROD';
            const colorCode = e.target.value.substring(0, 3).toUpperCase();
            
            if (colorCode && !skuInput.dataset.manual) {
                skuInput.value = `${baseCode}-${colorCode}`;
            }
        }
    });
    
    // Marcar SKU como editado manualmente
    document.getElementById('variants-container').addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('[sku]')) {
            e.target.dataset.manual = 'true';
        }
    });
});

function createVariantHTML(index) {
    // Obtener warehouses desde PHP para generar los campos de stock
    const warehouses = @json($warehouses);
    
    let stockInputs = '';
    warehouses.forEach(warehouse => {
        const warehouseName = warehouse.nickname ? 
            `${warehouse.name} (${warehouse.nickname})` : 
            warehouse.name;
        
        stockInputs += `
            <div class="col-md-6 form-group">
                <label class="form-label small">${warehouseName}</label>
                <input type="number" 
                       name="variants[${index}][stocks][${warehouse.id}]" 
                       class="form-control form-control-sm" 
                       min="0" 
                       placeholder="0"
                       value="0">
            </div>
        `;
    });

    return `
        <div class="variant-item border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Variante ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-variant">
                    <i class="feather icon-trash-2"></i> Eliminar
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="form-label">SKU de la Variante <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="variants[${index}][sku]" 
                           class="form-control" 
                           required
                           placeholder="Se genera automáticamente"
                           style="text-transform: uppercase">
                </div>
                <div class="col-md-6 form-group">
                    <label class="form-label">Nombre del Color/Variante</label>
                    <input type="text" 
                           name="variants[${index}][color_name]" 
                           class="form-control" 
                           placeholder="Ej: Azul, Negro, Rojo">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="form-label">Precio <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" 
                               name="variants[${index}][price]" 
                               class="form-control" 
                               step="0.01" 
                               min="0"
                               required 
                               placeholder="0.00">
                    </div>
                    <small class="form-text text-muted">Si no se especifica, usa el precio base del producto</small>
                </div>
                <div class="col-md-6 form-group">
                    <label class="form-label">Imagen de la Variante</label>
                    <img id="variant-preview-${index}" 
                         style="max-height: 80px; display: none;" 
                         class="img-thumbnail mb-2">
                    <input type="file" 
                           name="variants[${index}][image]" 
                           class="form-control-file" 
                           accept="image/*"
                           onchange="previewVariantImage(this, ${index})">
                    <small class="form-text text-muted">Imagen específica para esta variante</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <label class="form-label">Stock por Almacén</label>
                    <div class="row">
                        ${stockInputs}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Función para previsualizar imágenes
function previewVariantImage(input, index) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(`variant-preview-${index}`);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Validación del formulario antes de enviar
document.querySelector('form').addEventListener('submit', function(e) {
    const variants = document.querySelectorAll('.variant-item');
    let hasError = false;
    
    variants.forEach((variant, index) => {
        const skuInput = variant.querySelector('input[name*="[sku]"]');
        if (!skuInput.value.trim()) {
            skuInput.classList.add('is-invalid');
            hasError = true;
        } else {
            skuInput.classList.remove('is-invalid');
        }
    });
    
    if (hasError) {
        e.preventDefault();
    }
});
</script>
@endsection