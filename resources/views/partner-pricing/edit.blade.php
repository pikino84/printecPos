@extends('layouts.app')
@section('title', 'Configurar Pricing - ' . $partner->name)

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Configurar Pricing</h5>
            <p class="text-muted mb-0">{{ $partner->name }}</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Formulario de Configuración -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Configuración de Precios</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('partner-pricing.update', $partner) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Markup Percentage -->
                    <div class="mb-4">
                        <label for="markup_percentage" class="form-label">
                            Porcentaje de Markup <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control @error('markup_percentage') is-invalid @enderror" 
                                   id="markup_percentage" 
                                   name="markup_percentage" 
                                   value="{{ old('markup_percentage', $pricing->markup_percentage) }}" 
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   required>
                            <span class="input-group-text">%</span>
                            @error('markup_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            Ganancia del partner sobre el precio que recibe de Printec (después de descuento por nivel)
                        </small>
                    </div>

                    <!-- Nivel Actual -->
                    <div class="mb-4">
                        <label for="current_tier_id" class="form-label">Nivel de Precio</label>
                        <select class="form-control @error('current_tier_id') is-invalid @enderror" 
                                id="current_tier_id" 
                                name="current_tier_id">
                            <option value="">Sin nivel asignado</option>
                            @foreach($tiers as $tier)
                                <option value="{{ $tier->id }}" 
                                    {{ old('current_tier_id', $pricing->current_tier_id) == $tier->id ? 'selected' : '' }}>
                                    {{ $tier->name }} 
                                    ({{ number_format($tier->discount_percentage, 2) }}% descuento)
                                    - ${{ number_format($tier->min_monthly_purchases, 2) }}
                                    @if($tier->max_monthly_purchases)
                                        - ${{ number_format($tier->max_monthly_purchases, 2) }}
                                    @else
                                        en adelante
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('current_tier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            El nivel determina el descuento que recibe sobre el markup de Printec
                        </small>
                    </div>

                    <!-- Override Manual -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="manual_tier_override" 
                                   name="manual_tier_override" 
                                   value="1"
                                   {{ old('manual_tier_override', $pricing->manual_tier_override) ? 'checked' : '' }}>
                            <label class="form-check-label" for="manual_tier_override">
                                <strong>Bloquear asignación automática de nivel</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Si está activo, el nivel no cambiará automáticamente cada mes según las compras
                        </small>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Guardar Configuración
                    </button>
                    <a href="{{ route('partner-pricing.index') }}" class="btn btn-secondary">
                        <i class="feather icon-x"></i> Cancelar
                    </a>
                </form>
            </div>
        </div>

        <!-- Simulador de Precios -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">📊 Simulador de Precio</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="simulate_price" class="form-label">Precio Base del Producto</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" 
                               class="form-control" 
                               id="simulate_price" 
                               value="100"
                               step="0.01"
                               min="0">
                    </div>
                </div>

                <div id="price-breakdown" class="border rounded p-3 bg-light">
                    <!-- Se llenará con JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Partner -->
    <div class="col-md-4">
        <!-- Datos Actuales -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Información Actual</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Tipo:</th>
                        <td>
                            <span class="badge bg-secondary">{{ $partner->type }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Markup Actual:</th>
                        <td>{{ number_format($pricing->markup_percentage, 2) }}%</td>
                    </tr>
                    <tr>
                        <th>Nivel Actual:</th>
                        <td>
                            @if($pricing->currentTier)
                                <span class="badge bg-success">{{ $pricing->currentTier->name }}</span>
                                <br>
                                <small>{{ number_format($pricing->currentTier->discount_percentage, 2) }}% desc.</small>
                            @else
                                <span class="badge bg-secondary">Sin nivel</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Override Manual:</th>
                        <td>
                            @if($pricing->manual_tier_override)
                                <span class="badge bg-warning">
                                    <i class="feather icon-lock"></i> Activo
                                </span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Compras -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Compras</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Mes Actual:</th>
                        <td>
                            <strong>${{ number_format($pricing->current_month_purchases, 2) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Mes Anterior:</th>
                        <td>${{ number_format($pricing->last_month_purchases, 2) }}</td>
                    </tr>
                    @if($pricing->tier_assigned_at)
                    <tr>
                        <th>Nivel Asignado:</th>
                        <td>
                            <small>{{ $pricing->tier_assigned_at->format('d/m/Y') }}</small>
                        </td>
                    </tr>
                    @endif
                </table>

                @if($pricing->current_month_purchases > 0)
                <form action="{{ route('partner-pricing.reset-purchases', $partner) }}" 
                      method="POST" 
                      onsubmit="return confirm('¿Resetear compras del mes actual?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="feather icon-refresh-cw"></i> Resetear Compras
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Acciones -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Acciones</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('partner-pricing.history', $partner) }}" class="btn btn-info w-100 mb-2">
                    <i class="feather icon-clock"></i> Ver Historial de Niveles
                </a>
                <a href="{{ route('partners.show', $partner) }}" class="btn btn-secondary w-100">
                    <i class="feather icon-eye"></i> Ver Perfil del Partner
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markupInput = document.getElementById('markup_percentage');
    const tierSelect = document.getElementById('current_tier_id');
    const priceInput = document.getElementById('simulate_price');
    const breakdown = document.getElementById('price-breakdown');
    
    const printecMarkup = {{ \App\Models\PricingSetting::get('printec_markup', 52) }};
    const taxRate = {{ \App\Models\PricingSetting::get('tax_rate', 16) }};
    
    const tiers = @json($tiers->keyBy('id'));
    
    function updateSimulation() {
        const basePrice = parseFloat(priceInput.value) || 0;
        const partnerMarkup = parseFloat(markupInput.value) || 0;
        const tierId = tierSelect.value;
        const tier = tierId ? tiers[tierId] : null;
        const discount = tier ? parseFloat(tier.discount_percentage) : 0;
        
        // Cálculos
        const withPrintecMarkup = basePrice + (basePrice * printecMarkup / 100);
        const afterDiscount = withPrintecMarkup - (withPrintecMarkup * discount / 100);
        const withPartnerMarkup = afterDiscount + (afterDiscount * partnerMarkup / 100);
        const withTax = withPartnerMarkup * (1 + taxRate / 100);
        
        breakdown.innerHTML = `
            <table class="table table-sm mb-0">
                <tr>
                    <td>Precio Base:</td>
                    <td class="text-right"><strong>$${basePrice.toFixed(2)}</strong></td>
                </tr>
                <tr>
                    <td>+ Markup Printec (${printecMarkup}%):</td>
                    <td class="text-right">$${withPrintecMarkup.toFixed(2)}</td>
                </tr>
                ${tier ? `
                <tr class="table-success">
                    <td>- Descuento ${tier.name} (${discount}%):</td>
                    <td class="text-right"><strong>$${afterDiscount.toFixed(2)}</strong></td>
                </tr>
                ` : ''}
                <tr>
                    <td>+ Markup Partner (${partnerMarkup}%):</td>
                    <td class="text-right"><strong>$${withPartnerMarkup.toFixed(2)}</strong></td>
                </tr>
                <tr class="table-info">
                    <td><strong>+ IVA (${taxRate}%):</strong></td>
                    <td class="text-right"><strong class="text-success">$${withTax.toFixed(2)}</strong></td>
                </tr>
            </table>
        `;
    }
    
    markupInput.addEventListener('input', updateSimulation);
    tierSelect.addEventListener('change', updateSimulation);
    priceInput.addEventListener('input', updateSimulation);
    
    // Inicializar
    updateSimulation();
});
</script>
@endsection