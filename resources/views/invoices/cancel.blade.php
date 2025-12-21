@extends('layouts.app')

@section('title', 'Cancelar Factura')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h4><i class="feather icon-x-circle text-danger"></i> Cancelar Factura</h4>
            <p class="text-muted">Factura: {{ $invoice->full_folio }} | UUID: {{ $invoice->uuid }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Solicitud de Cancelación</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="feather icon-alert-triangle"></i>
                        <strong>Importante:</strong> La cancelación de facturas es irreversible y quedará registrada ante el SAT.
                    </div>

                    <form action="{{ route('invoices.cancel', $invoice) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="cancellation_reason">Motivo de Cancelación <span class="text-danger">*</span></label>
                            <select name="cancellation_reason" id="cancellation_reason" class="form-control @error('cancellation_reason') is-invalid @enderror" required>
                                <option value="">Seleccione un motivo...</option>
                                <option value="01">01 - Comprobante emitido con errores con relación</option>
                                <option value="02">02 - Comprobante emitido con errores sin relación</option>
                                <option value="03">03 - No se llevó a cabo la operación</option>
                                <option value="04">04 - Operación nominativa relacionada en una factura global</option>
                            </select>
                            @error('cancellation_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" id="replacement_uuid_group" style="display: none;">
                            <label for="replacement_uuid">UUID de Factura de Reemplazo <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="replacement_uuid"
                                   id="replacement_uuid"
                                   class="form-control @error('replacement_uuid') is-invalid @enderror"
                                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                   pattern="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}">
                            @error('replacement_uuid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Ingrese el UUID de la factura que sustituye a esta.
                            </small>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro de cancelar esta factura? Esta acción es irreversible.')">
                                <i class="feather icon-x-circle"></i> Confirmar Cancelación
                            </button>
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
                                Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información de la Factura</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Folio:</td>
                            <td><strong>{{ $invoice->full_folio }}</strong></td>
                        </tr>
                        <tr>
                            <td>UUID:</td>
                            <td><code class="small">{{ $invoice->uuid }}</code></td>
                        </tr>
                        <tr>
                            <td>Fecha Timbrado:</td>
                            <td>{{ $invoice->stamped_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Receptor:</td>
                            <td>{{ $invoice->receptor_name }}</td>
                        </tr>
                        <tr>
                            <td>RFC Receptor:</td>
                            <td>{{ $invoice->receptor_rfc }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total:</strong></td>
                            <td><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Motivos de Cancelación SAT</h6>
                </div>
                <div class="card-body small">
                    <p><strong>01:</strong> Cuando la factura tiene errores y ya se emitió una nueva que la sustituye. Se requiere el UUID de la factura de reemplazo.</p>
                    <p><strong>02:</strong> Cuando la factura tiene errores pero no se ha emitido una de reemplazo.</p>
                    <p><strong>03:</strong> Cuando la operación comercial no se realizó.</p>
                    <p class="mb-0"><strong>04:</strong> Para operaciones relacionadas en facturas globales.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('cancellation_reason').addEventListener('change', function() {
    const replacementGroup = document.getElementById('replacement_uuid_group');
    const replacementInput = document.getElementById('replacement_uuid');

    if (this.value === '01') {
        replacementGroup.style.display = 'block';
        replacementInput.required = true;
    } else {
        replacementGroup.style.display = 'none';
        replacementInput.required = false;
        replacementInput.value = '';
    }
});
</script>
@endpush
@endsection
