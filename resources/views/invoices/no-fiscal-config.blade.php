@extends('layouts.app')

@section('title', 'Configuración Fiscal Requerida')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h4><i class="feather icon-alert-triangle text-warning"></i> Configuración Fiscal Requerida</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-dark">
                        <i class="feather icon-settings"></i> Configuración Necesaria para Facturar
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="feather icon-file-text" style="font-size: 4rem; color: #ffc107;"></i>
                    </div>

                    <div class="alert alert-warning">
                        <h5><i class="feather icon-alert-circle"></i> No se puede generar la factura</h5>
                        <p class="mb-0">
                            Para poder generar facturas de la cotización <strong>{{ $quote->quote_number }}</strong>,
                            es necesario configurar los datos fiscales de su empresa.
                        </p>
                    </div>

                    <h6 class="mb-3">Datos requeridos para facturar:</h6>
                    <ul class="list-group mb-4">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="feather icon-check-circle text-muted mr-3"></i>
                            <div>
                                <strong>Razón Social</strong>
                                <br><small class="text-muted">Nombre legal de su empresa como aparece en el SAT</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="feather icon-check-circle text-muted mr-3"></i>
                            <div>
                                <strong>RFC</strong>
                                <br><small class="text-muted">Registro Federal de Contribuyentes (12 o 13 caracteres)</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="feather icon-check-circle text-muted mr-3"></i>
                            <div>
                                <strong>Régimen Fiscal</strong>
                                <br><small class="text-muted">Código del régimen fiscal según el catálogo del SAT</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="feather icon-check-circle text-muted mr-3"></i>
                            <div>
                                <strong>Código Postal</strong>
                                <br><small class="text-muted">C.P. del domicilio fiscal (5 dígitos)</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="feather icon-check-circle text-muted mr-3"></i>
                            <div>
                                <strong>Certificados CSD</strong>
                                <br><small class="text-muted">Archivos .cer y .key para timbrado de facturas</small>
                            </div>
                        </li>
                    </ul>

                    <div class="alert alert-info">
                        <i class="feather icon-info"></i>
                        <strong>¿Cómo configurar?</strong>
                        <p class="mb-0 mt-2">
                            Vaya a <strong>Asociado → Razones Sociales</strong> y agregue o edite su entidad fiscal
                            con todos los datos requeridos para poder emitir facturas electrónicas (CFDI).
                        </p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('quotes.index') }}" class="btn btn-secondary">
                            <i class="feather icon-arrow-left"></i> Volver a Cotizaciones
                        </a>
                        <a href="{{ route('my-entities.index') }}" class="btn btn-primary">
                            <i class="feather icon-settings"></i> Configurar Datos Fiscales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal que se muestra automáticamente al cargar la página --}}
<div class="modal fade" id="fiscalConfigModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark">
                    <i class="feather icon-alert-triangle"></i> Configuración Fiscal Requerida
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="feather icon-settings" style="font-size: 3rem; color: #ffc107;"></i>
                </div>
                <p class="text-center">
                    <strong>No se encontraron datos fiscales configurados.</strong>
                </p>
                <p class="text-center text-muted">
                    Para generar facturas electrónicas (CFDI) es necesario configurar
                    la razón social, RFC, régimen fiscal y certificados de su empresa.
                </p>
                <hr>
                <p class="text-center mb-0">
                    <small>
                        Cotización: <strong>{{ $quote->quote_number }}</strong><br>
                        Total: <strong>${{ number_format($quote->total, 2) }}</strong>
                    </small>
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('quotes.index') }}" class="btn btn-secondary">
                    <i class="feather icon-arrow-left"></i> Volver
                </a>
                <a href="{{ route('my-entities.index') }}" class="btn btn-warning">
                    <i class="feather icon-settings"></i> Configurar Ahora
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('fiscalConfigModal'));
        modal.show();
    });
</script>
@endpush
@endsection
