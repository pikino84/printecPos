@extends('layouts.app')
@section('title', 'Editar Nivel de Precio')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Editar Nivel de Precio</h5>
            <p class="text-muted mb-0">{{ $pricingTier->name }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('pricing-tiers.update', $pricingTier) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nombre del Nivel <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $pricingTier->name) }}" 
                           required 
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Orden -->
                <div class="col-md-6 mb-3">
                    <label for="order" class="form-label">Orden de Visualización</label>
                    <input type="number" 
                           class="form-control @error('order') is-invalid @enderror" 
                           id="order" 
                           name="order" 
                           value="{{ old('order', $pricingTier->order) }}" 
                           min="0">
                    @error('order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Menor número aparece primero</small>
                </div>
            </div>

            <div class="row">
                <!-- Compras Mínimas -->
                <div class="col-md-6 mb-3">
                    <label for="min_monthly_purchases" class="form-label">Compras Mínimas Mensuales <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control @error('min_monthly_purchases') is-invalid @enderror" 
                               id="min_monthly_purchases" 
                               name="min_monthly_purchases" 
                               value="{{ old('min_monthly_purchases', $pricingTier->min_monthly_purchases) }}" 
                               step="0.01"
                               min="0"
                               required>
                        @error('min_monthly_purchases')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Compras Máximas -->
                <div class="col-md-6 mb-3">
                    <label for="max_monthly_purchases" class="form-label">Compras Máximas Mensuales</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control @error('max_monthly_purchases') is-invalid @enderror" 
                               id="max_monthly_purchases" 
                               name="max_monthly_purchases" 
                               value="{{ old('max_monthly_purchases', $pricingTier->max_monthly_purchases) }}" 
                               step="0.01"
                               min="0">
                        @error('max_monthly_purchases')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Dejar vacío para "sin límite"</small>
                </div>
            </div>

            <div class="row">
                <!-- Markup -->
                <div class="col-md-6 mb-3">
                    <label for="markup_percentage" class="form-label">Porcentaje de Markup <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">+</span>
                        <input type="number" 
                               class="form-control @error('markup_percentage') is-invalid @enderror" 
                               id="markup_percentage" 
                               name="markup_percentage" 
                               value="{{ old('markup_percentage', $pricingTier->markup_percentage) }}" 
                               step="0.01"
                               min="0"
                               max="100"
                               required>
                        <span class="input-group-text">%</span>
                        @error('markup_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Junior = 52%, resto = 16%</small>
                </div>

                <!-- Descuento -->
                <div class="col-md-6 mb-3">
                    <label for="discount_percentage" class="form-label">Porcentaje de Descuento <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">-</span>
                        <input type="number" 
                               class="form-control @error('discount_percentage') is-invalid @enderror" 
                               id="discount_percentage" 
                               name="discount_percentage" 
                               value="{{ old('discount_percentage', $pricingTier->discount_percentage) }}" 
                               step="0.01"
                               min="0"
                               max="100"
                               required>
                        <span class="input-group-text">%</span>
                        @error('discount_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">0%, 2%, 4%... hasta 12%</small>
                </div>
            </div>

            <div class="row">
                <!-- Descripción -->
                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3">{{ old('description', $pricingTier->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <!-- Estado Activo -->
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $pricingTier->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Nivel activo
                        </label>
                    </div>
                    <small class="form-text text-muted">Los niveles inactivos no se asignan automáticamente</small>
                </div>
            </div>

            <!-- Vista previa de fórmula -->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-info mb-0">
                        <strong>📊 Fórmula:</strong> 
                        <code id="formula-preview">{{ $pricingTier->formula }}</code>
                        <span class="float-right">
                            Precio ejemplo ($100): <strong id="price-preview">${{ number_format($pricingTier->calculatePriceWithTax(100), 2) }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('pricing-tiers.index') }}" class="btn btn-secondary">
                        <i class="feather icon-x"></i> Cancelar
                    </a>
                    
                    @if($pricingTier->partners()->count() == 0)
                    <button type="button" 
                            class="btn btn-danger float-right" 
                            onclick="confirmDelete()">
                        <i class="feather icon-trash-2"></i> Eliminar Nivel
                    </button>
                    @endif
                </div>
            </div>
        </form>
        
        @if($pricingTier->partners()->count() == 0)
        <form id="delete-form" 
              action="{{ route('pricing-tiers.destroy', $pricingTier) }}" 
              method="POST" 
              style="display: none;">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </div>
</div>

<!-- Información del Nivel -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">Información del Nivel</h6>
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Creado:</strong> 
                    {{ $pricingTier->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Última actualización:</strong> 
                    {{ $pricingTier->updated_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <p class="mb-0">
                    <strong>Partners asignados:</strong> 
                    <span class="badge bg-info">{{ $pricingTier->partners()->count() }}</span>
                </p>
                @if($pricingTier->partners()->count() > 0)
                <small class="text-muted">No se puede eliminar este nivel porque tiene partners asignados</small>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const minInput = document.getElementById('min_monthly_purchases');
    const maxInput = document.getElementById('max_monthly_purchases');
    const markupInput = document.getElementById('markup_percentage');
    const discountInput = document.getElementById('discount_percentage');
    const formulaPreview = document.getElementById('formula-preview');
    const pricePreview = document.getElementById('price-preview');
    
    // Validación min/max
    maxInput.addEventListener('input', function() {
        const min = parseFloat(minInput.value) || 0;
        const max = parseFloat(maxInput.value) || 0;
        
        if (max > 0 && max <= min) {
            maxInput.setCustomValidity('El máximo debe ser mayor que el mínimo');
        } else {
            maxInput.setCustomValidity('');
        }
    });
    
    // Actualizar fórmula en tiempo real
    function updateFormula() {
        const markup = parseFloat(markupInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        
        let formula = '';
        if (discount > 0) {
            formula = `(Price + ${markup}%) - ${discount}% + IVA`;
        } else {
            formula = `Price + ${markup}% + IVA`;
        }
        formulaPreview.textContent = formula;
        
        // Calcular precio ejemplo
        const basePrice = 100;
        const withMarkup = basePrice * (1 + markup / 100);
        const afterDiscount = withMarkup * (1 - discount / 100);
        const withTax = afterDiscount * 1.16;
        pricePreview.textContent = '$' + withTax.toFixed(2);
    }
    
    markupInput.addEventListener('input', updateFormula);
    discountInput.addEventListener('input', updateFormula);
});

// Confirmación de eliminación
function confirmDelete() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            html: '¿Deseas eliminar este nivel de precio?<br><br>Esta acción no se puede deshacer.',
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
            text: '¿Deseas eliminar este nivel de precio?\n\nEsta acción no se puede deshacer.',
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
        if (confirm('¿Estás seguro de que deseas eliminar este nivel de precio?')) {
            document.getElementById('delete-form').submit();
        }
    }
}
</script>
@endsection