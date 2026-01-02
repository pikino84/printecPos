@extends('layouts.app')
@section('title', 'Mi Ganancia')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h5>Mi Ganancia</h5>
            <p class="text-muted mb-0">Configura tu porcentaje de ganancia sobre los productos</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Porcentaje de Markup</h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form action="{{ route('my-markup.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="markup_percentage" class="form-label">
                            Porcentaje de Ganancia <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('markup_percentage') is-invalid @enderror"
                                   id="markup_percentage"
                                   name="markup_percentage"
                                   value="{{ old('markup_percentage', $pricing->markup_percentage ?? 0) }}"
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
                            Este porcentaje se suma al precio que pagas a Printec para calcular el precio final de venta a tus clientes.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-save"></i> Guardar
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
                    <!-- Se llenará con JavaScript -->
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Información</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th>Tu Markup Actual:</th>
                        <td><strong>{{ number_format($pricing->markup_percentage ?? 0, 2) }}%</strong></td>
                    </tr>
                    @if($pricing->currentTier)
                    <tr>
                        <th>Tu Nivel:</th>
                        <td>
                            <span class="badge bg-success">{{ $pricing->currentTier->name }}</span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markupInput = document.getElementById('markup_percentage');
    const priceInput = document.getElementById('simulate_price');
    const breakdown = document.getElementById('price-breakdown');

    const taxRate = {{ \App\Models\PricingSetting::get('tax_rate', 16) }};

    function updateSimulation() {
        const basePrice = parseFloat(priceInput.value) || 0;
        const partnerMarkup = parseFloat(markupInput.value) || 0;

        // Precio con markup del partner
        const withPartnerMarkup = basePrice + (basePrice * partnerMarkup / 100);
        const withTax = withPartnerMarkup * (1 + taxRate / 100);

        breakdown.innerHTML = `
            <table class="table table-sm mb-0">
                <tr>
                    <td>Tu Precio (lo que pagas a Printec):</td>
                    <td class="text-right"><strong>$${basePrice.toFixed(2)}</strong></td>
                </tr>
                <tr>
                    <td>+ Tu Ganancia (${partnerMarkup}%):</td>
                    <td class="text-right">$${withPartnerMarkup.toFixed(2)}</td>
                </tr>
                <tr class="table-info">
                    <td><strong>+ IVA (${taxRate}%):</strong></td>
                    <td class="text-right"><strong class="text-success">$${withTax.toFixed(2)}</strong></td>
                </tr>
            </table>
            <div class="mt-2 text-center">
                <small class="text-muted">Precio Final para tu Cliente</small>
            </div>
        `;
    }

    markupInput.addEventListener('input', updateSimulation);
    priceInput.addEventListener('input', updateSimulation);

    // Inicializar
    updateSimulation();
});
</script>
@endsection
