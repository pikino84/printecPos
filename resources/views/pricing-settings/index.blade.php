@extends('layouts.app')
@section('title', 'Configuración de Pricing')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Configuración de Pricing</h5>
            <p class="text-muted mb-0">Parámetros globales del sistema de precios</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Parámetros de Precio</h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('pricing-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="printec_markup" class="form-label">
                            Markup Printec <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('printec_markup') is-invalid @enderror"
                                   id="printec_markup"
                                   name="printec_markup"
                                   value="{{ old('printec_markup', $settings['printec_markup']) }}"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   required>
                            <span class="input-group-text">%</span>
                            @error('printec_markup')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            Porcentaje de ganancia de Printec sobre el precio base de los productos
                        </small>
                    </div>

                    <div class="mb-4">
                        <label for="tax_rate" class="form-label">
                            IVA <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('tax_rate') is-invalid @enderror"
                                   id="tax_rate"
                                   name="tax_rate"
                                   value="{{ old('tax_rate', $settings['tax_rate']) }}"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   required>
                            <span class="input-group-text">%</span>
                            @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            Porcentaje de impuesto aplicado al precio final
                        </small>
                    </div>

                    <hr class="my-4">

                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Guardar Configuración
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Simulador de Precio</h6>
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
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Información</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading"><i class="feather icon-info"></i> Cálculo de Precios</h6>
                    <hr>
                    <p class="mb-1"><strong>1.</strong> Precio Base del producto</p>
                    <p class="mb-1"><strong>2.</strong> + Markup Printec (ganancia de Printec)</p>
                    <p class="mb-1"><strong>3.</strong> - Descuento por Nivel (según tier del partner)</p>
                    <p class="mb-1"><strong>4.</strong> + Markup del Partner (ganancia del asociado)</p>
                    <p class="mb-0"><strong>5.</strong> + IVA (precio final al cliente)</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markupInput = document.getElementById('printec_markup');
    const taxInput = document.getElementById('tax_rate');
    const priceInput = document.getElementById('simulate_price');
    const breakdown = document.getElementById('price-breakdown');

    function updateSimulation() {
        const basePrice = parseFloat(priceInput.value) || 0;
        const printecMarkup = parseFloat(markupInput.value) || 0;
        const taxRate = parseFloat(taxInput.value) || 0;

        const withPrintecMarkup = basePrice + (basePrice * printecMarkup / 100);
        const withTax = withPrintecMarkup * (1 + taxRate / 100);

        breakdown.innerHTML = `
            <table class="table table-sm mb-0">
                <tr>
                    <td>Precio Base:</td>
                    <td class="text-end"><strong>$${basePrice.toFixed(2)}</strong></td>
                </tr>
                <tr>
                    <td>+ Markup Printec (${printecMarkup}%):</td>
                    <td class="text-end">$${withPrintecMarkup.toFixed(2)}</td>
                </tr>
                <tr class="table-secondary">
                    <td><em>- Descuento por Nivel (variable)</em></td>
                    <td class="text-end"><em>Según tier</em></td>
                </tr>
                <tr class="table-secondary">
                    <td><em>+ Markup Partner (variable)</em></td>
                    <td class="text-end"><em>Según partner</em></td>
                </tr>
                <tr class="table-info">
                    <td><strong>+ IVA (${taxRate}%):</strong></td>
                    <td class="text-end"><strong class="text-success">$${withTax.toFixed(2)}</strong></td>
                </tr>
            </table>
            <small class="text-muted d-block mt-2">
                * El precio final incluye descuento por nivel y markup del partner
            </small>
        `;
    }

    markupInput.addEventListener('input', updateSimulation);
    taxInput.addEventListener('input', updateSimulation);
    priceInput.addEventListener('input', updateSimulation);

    updateSimulation();
});
</script>
@endsection
