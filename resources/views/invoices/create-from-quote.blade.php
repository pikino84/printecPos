@extends('layouts.app')

@section('title', 'Generar Factura')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h4><i class="feather icon-file-plus"></i> Generar Factura(s)</h4>
            <p class="text-muted">Cotización: {{ $quote->quote_number }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Configuración de Facturación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('invoices.store-from-quote', $quote) }}" method="POST">
                        @csrf

                        {{-- Entidad Emisora --}}
                        <div class="form-group">
                            <label for="partner_entity_id">Entidad Emisora (Razón Social) <span class="text-danger">*</span></label>
                            <select name="partner_entity_id" id="partner_entity_id" class="form-control @error('partner_entity_id') is-invalid @enderror" required>
                                <option value="">Seleccione una entidad...</option>
                                @foreach($entities as $entity)
                                    <option value="{{ $entity->id }}"
                                            data-rfc="{{ $entity->rfc }}"
                                            data-regime="{{ $entity->fiscal_regime_label }}"
                                            data-can-issue="{{ $entity->canIssueInvoices() ? '1' : '0' }}">
                                        {{ $entity->razon_social }} ({{ $entity->rfc }})
                                        @if(!$entity->canIssueInvoices())
                                            - Configuración incompleta
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('partner_entity_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="entity-info"></small>
                        </div>

                        {{-- Tipo de Pago --}}
                        <div class="form-group">
                            <label>Esquema de Pago <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="payment_full" name="payment_split" value="full" class="custom-control-input" checked>
                                        <label class="custom-control-label" for="payment_full">
                                            <strong>Pago Completo (100%)</strong>
                                            <br>
                                            <small class="text-muted">Una sola factura por ${{ number_format($quote->total, 2) }}</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="payment_split" name="payment_split" value="split" class="custom-control-input">
                                        <label class="custom-control-label" for="payment_split">
                                            <strong>Dos Pagos (50% + 50%)</strong>
                                            <br>
                                            <small class="text-muted">Dos facturas de ${{ number_format($quote->total / 2, 2) }} cada una</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Forma de Pago --}}
                        <div class="form-group">
                            <label for="payment_form">Forma de Pago <span class="text-danger">*</span></label>
                            <select name="payment_form" id="payment_form" class="form-control @error('payment_form') is-invalid @enderror" required>
                                <option value="99">99 - Por definir</option>
                                <option value="01">01 - Efectivo</option>
                                <option value="02">02 - Cheque nominativo</option>
                                <option value="03">03 - Transferencia electrónica de fondos</option>
                                <option value="04">04 - Tarjeta de crédito</option>
                                <option value="28">28 - Tarjeta de débito</option>
                            </select>
                            @error('payment_form')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Uso del CFDI --}}
                        <div class="form-group">
                            <label for="cfdi_use">Uso del CFDI <span class="text-danger">*</span></label>
                            <select name="cfdi_use" id="cfdi_use" class="form-control @error('cfdi_use') is-invalid @enderror" required>
                                <option value="G03">G03 - Gastos en general</option>
                                <option value="G01">G01 - Adquisición de mercancías</option>
                                <option value="G02">G02 - Devoluciones, descuentos o bonificaciones</option>
                                <option value="I01">I01 - Construcciones</option>
                                <option value="I02">I02 - Mobiliario y equipo de oficina por inversiones</option>
                                <option value="I03">I03 - Equipo de transporte</option>
                                <option value="I04">I04 - Equipo de cómputo y accesorios</option>
                                <option value="I08">I08 - Otra maquinaria y equipo</option>
                                <option value="P01">P01 - Por definir</option>
                                <option value="S01">S01 - Sin efectos fiscales</option>
                            </select>
                            @error('cfdi_use')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success">
                                <i class="feather icon-check"></i> Generar Factura(s)
                            </button>
                            <a href="{{ route('quotes.show', $quote) }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Resumen de la Cotización --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resumen de Cotización</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Número:</td>
                            <td class="text-right"><strong>{{ $quote->quote_number }}</strong></td>
                        </tr>
                        <tr>
                            <td>Cliente:</td>
                            <td class="text-right">{{ $quote->client_razon_social ?? $quote->client_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>RFC:</td>
                            <td class="text-right">{{ $quote->client_rfc ?? 'XAXX010101000' }}</td>
                        </tr>
                        <tr>
                            <td>Items:</td>
                            <td class="text-right">{{ $quote->items->count() }}</td>
                        </tr>
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">${{ number_format($quote->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>IVA (16%):</td>
                            <td class="text-right">${{ number_format($quote->tax, 2) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total:</strong></td>
                            <td class="text-right"><strong>${{ number_format($quote->total, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Datos del Receptor --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Datos del Receptor</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $quote->client_razon_social ?? $quote->client_name ?? 'PUBLICO EN GENERAL' }}</strong></p>
                    <p class="mb-1 text-muted">RFC: {{ $quote->client_rfc ?? 'XAXX010101000' }}</p>
                    @if($quote->client_email)
                        <p class="mb-0 text-muted">Email: {{ $quote->client_email }}</p>
                    @endif
                </div>
            </div>

            {{-- Advertencia --}}
            <div class="alert alert-warning">
                <i class="feather icon-alert-triangle"></i>
                <strong>Importante:</strong> Una vez generadas las facturas, deberá timbrarlas para que sean válidas fiscalmente.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('partner_entity_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const infoElement = document.getElementById('entity-info');

    if (selected.value) {
        const canIssue = selected.dataset.canIssue === '1';
        const regime = selected.dataset.regime;

        if (canIssue) {
            infoElement.innerHTML = '<span class="text-success">Régimen: ' + regime + '</span>';
        } else {
            infoElement.innerHTML = '<span class="text-danger">Esta entidad no tiene configuración fiscal completa. Configure RFC, régimen fiscal y código postal.</span>';
        }
    } else {
        infoElement.innerHTML = '';
    }
});
</script>
@endpush
@endsection
