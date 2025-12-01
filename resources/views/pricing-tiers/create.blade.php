@extends('layouts.app')
@section('title', 'Crear Nivel de Precio')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Crear Nivel de Precio</h5>
            <p class="text-muted mb-0">Define un nuevo nivel de descuento por volumen de compras</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('pricing-tiers.store') }}" method="POST">
            @csrf

            <div class="row">
                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nombre del Nivel <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required 
                           placeholder="Ej: Oro A"
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
                           value="{{ old('order', 0) }}" 
                           min="0"
                           placeholder="Ej: 10">
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
                               value="{{ old('min_monthly_purchases', 0) }}" 
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
                               value="{{ old('max_monthly_purchases') }}" 
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
                               value="{{ old('markup_percentage', 16) }}" 
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
                               value="{{ old('discount_percentage', 0) }}" 
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
                              rows="3"
                              placeholder="Descripción opcional del nivel">{{ old('description') }}</textarea>
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
                               {{ old('is_active', true) ? 'checked' : '' }}>
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
                        <code id="formula-preview">Price + 16% + IVA</code>
                        <span class="float-right">
                            Precio ejemplo ($100): <strong id="price-preview">$134.56</strong>
                        </span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Crear Nivel
                    </button>
                    <a href="{{ route('pricing-tiers.index') }}" class="btn btn-secondary">
                        <i class="feather icon-x"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Información de ayuda -->
<div class="card mt-3">
    <div class="card-body">
        <h6 class="mb-3">💡 Consejos para Crear Niveles</h6>
        <ul class="mb-0">
            <li><strong>Orden:</strong> Usa números como 10, 20, 30... para poder insertar niveles intermedios después</li>
            <li><strong>Rangos:</strong> Asegúrate de que los rangos no se solapen entre diferentes niveles</li>
            <li><strong>Máximo sin límite:</strong> Deja el campo de compras máximas vacío para el nivel más alto</li>
            <li><strong>Markup:</strong> Junior usa 52%, el resto de niveles usa 16%</li>
            <li><strong>Ejemplo:</strong> Nivel "Oro A" con $400,001 - $600,000, markup 16% y descuento 12%</li>
        </ul>
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
    
    // Inicializar
    updateFormula();
});
</script>
@endsection