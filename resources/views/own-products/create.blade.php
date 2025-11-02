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
                {{-- ⚠️ ALERTA: Sin almacenes --}}
                @if(!$hasWarehouses)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong><i class="feather icon-alert-triangle"></i> Atención:</strong>
                    No tienes almacenes configurados. Contacta al administrador para crear al menos un almacén antes de agregar productos.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
                <form action="{{ route('own-products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                    @csrf
                    
                    <div class="row">
                        <!-- Información básica -->
                        <div class="col-lg-8">
                            {{-- 🆕 CARD: PROVEEDOR Y ALMACÉN --}}
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
                                                @forelse($partners as $partner)
                                                <option value="{{ $partner->id }}"
                                                        data-type="{{ $partner->type }}"
                                                        {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                                    {{ $partner->name }} ({{ $partner->type }})
                                                </option>
                                                @empty
                                                <option value="" disabled>No hay proveedores elegibles</option>
                                                @endforelse
                                            </select>
                                            @error('partner_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted" id="partner-info">
                                                <i class="feather icon-info"></i> Selecciona el proveedor del producto
                                            </small>
                                        </div>

                                        <div class="col-md-6 form-group" id="warehouse-group" style="display: none;">
                                            <label class="form-label">
                                                Almacén 
                                                <span class="text-danger" id="warehouse-required">*</span>
                                            </label>
                                            <select class="form-control @error('warehouse_id') is-invalid @enderror" 
                                                    name="warehouse_id"
                                                    id="warehouse_id">
                                                <option value="">Seleccionar almacén</option>
                                            </select>
                                            @error('warehouse_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted" id="warehouse-help"></small>
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
                                            <label class="form-label">Dimensiones</label>
                                            <input type="text" 
                                                   class="form-control @error('dimensions') is-invalid @enderror" 
                                                   name="dimensions" 
                                                   value="{{ old('dimensions') }}"
                                                   placeholder="Largo x Ancho x Alto">
                                            @error('dimensions')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Peso</label>
                                            <input type="text" 
                                                   class="form-control @error('weight') is-invalid @enderror" 
                                                   name="weight" 
                                                   value="{{ old('weight') }}"
                                                   placeholder="Ej: 100g">
                                            @error('weight')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label class="form-label">Área de Impresión</label>
                                            <input type="text" 
                                                   class="form-control @error('print_area') is-invalid @enderror" 
                                                   name="print_area" 
                                                   value="{{ old('print_area') }}"
                                                   placeholder="Ej: 5x8cm">
                                            @error('print_area')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha -->
                        <div class="col-lg-4">
                            <!-- Imagen -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Imagen Principal</h5>
                                </div>
                                <div class="card-block">
                                    <div class="form-group">
                                        <input type="file" 
                                               class="form-control @error('main_image') is-invalid @enderror" 
                                               name="main_image"
                                               accept="image/*">
                                        @error('main_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">JPG, PNG, GIF (max 2MB)</small>
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
                                            <option value="">Seleccionar categoría</option>
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

                            {{-- 🆕 INVENTARIO INICIAL - Solo si NO es asociado --}}
                            <div class="card" id="inventory-card" style="display: none;">
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
                                    <button type="submit" 
                                        class="btn btn-primary btn-sm" 
                                        @if(!$hasWarehouses) disabled @endif>
                                        <i class="feather icon-save"></i> Crear Producto
                                    </button>

                                    @if(!$hasWarehouses)
                                    <small class="text-danger d-block mt-2">
                                        <i class="feather icon-info"></i> Necesitas al menos un almacén para crear productos
                                    </small>
                                    @endif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const partnerSelect = document.getElementById('partner_id');
    const warehouseGroup = document.getElementById('warehouse-group');
    const warehouseSelect = document.getElementById('warehouse_id');
    const warehouseRequired = document.getElementById('warehouse-required');
    const warehouseHelp = document.getElementById('warehouse-help');
    const partnerInfo = document.getElementById('partner-info');
    const inventoryCard = document.getElementById('inventory-card');

    partnerSelect.addEventListener('change', function() {
        const partnerId = this.value;
        
        // Limpiar
        warehouseSelect.innerHTML = '<option value="">Seleccionar almacén</option>';
        warehouseGroup.style.display = 'none';
        warehouseSelect.removeAttribute('required');
        partnerInfo.innerHTML = '<i class="feather icon-info"></i> Selecciona el proveedor del producto';
        inventoryCard.style.display = 'none';

        if (!partnerId) {
            return;
        }

        // Loading
        partnerInfo.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cargando información del proveedor...';

        // AJAX
        fetch(`/api/partners/${partnerId}/warehouses`)
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar');
                return response.json();
            })
            .then(data => {
                console.log('Partner data:', data);

                partnerInfo.innerHTML = `<strong>${data.type_label}</strong> - ${data.type_description}`;

                if (data.requires_warehouse) {
                    // Proveedor o Mixto: REQUIERE almacén
                    warehouseGroup.style.display = 'block';
                    warehouseSelect.setAttribute('required', 'required');
                    warehouseRequired.style.display = 'inline';
                    inventoryCard.style.display = 'block';
                    
                    if (data.warehouses && data.warehouses.length > 0) {
                        data.warehouses.forEach(warehouse => {
                            const option = document.createElement('option');
                            option.value = warehouse.id;
                            let text = warehouse.name;
                            if (warehouse.city) text += ` (${warehouse.city})`;
                            option.textContent = text;
                            warehouseSelect.appendChild(option);
                        });
                        warehouseHelp.innerHTML = '<i class="feather icon-check text-success"></i> Selecciona el almacén donde se encuentra el producto.';
                    } else {
                        warehouseHelp.innerHTML = '<strong class="text-warning"><i class="feather icon-alert-triangle"></i> Este proveedor no tiene almacenes. Crea uno primero.</strong>';
                        warehouseSelect.innerHTML = '<option value="">No hay almacenes disponibles</option>';
                    }
                } else {
                    // Asociado: NO requiere almacén
                    warehouseGroup.style.display = 'none';
                    warehouseSelect.removeAttribute('required');
                    inventoryCard.style.display = 'none';
                    partnerInfo.innerHTML += '<br><span class="text-success"><i class="feather icon-check"></i> No requiere almacén para productos propios.</span>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                partnerInfo.innerHTML = '<span class="text-danger"><i class="feather icon-x"></i> Error al cargar información</span>';
                alert('Error al cargar los almacenes. Intenta nuevamente.');
            });
    });

    // Trigger si hay partner preseleccionado
    if (partnerSelect.value) {
        partnerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush