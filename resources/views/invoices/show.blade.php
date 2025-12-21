@extends('layouts.app')

@section('title', 'Factura ' . $invoice->full_folio)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-lg-6">
            <h4><i class="feather icon-file-text"></i> Factura {{ $invoice->full_folio }}</h4>
            <p class="text-muted mb-0">{{ $invoice->invoice_number }}</p>
        </div>
        <div class="col-lg-6 text-right">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="feather icon-arrow-left"></i> Volver
            </a>

            @if($invoice->isDraft())
                <form action="{{ route('invoices.stamp', $invoice) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('¿Timbrar esta factura?')">
                        <i class="feather icon-check"></i> Timbrar Factura
                    </button>
                </form>
            @endif

            @if($invoice->isStamped())
                <a href="{{ route('invoices.download-xml', $invoice) }}" class="btn btn-outline-primary">
                    <i class="feather icon-code"></i> Descargar XML
                </a>
                <a href="{{ route('invoices.download-pdf', $invoice) }}" class="btn btn-primary">
                    <i class="feather icon-download"></i> Descargar PDF
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Datos Generales --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Datos del CFDI</h5>
                    {!! $invoice->status_badge !!}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">Serie y Folio:</td>
                                    <td><strong>{{ $invoice->full_folio }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tipo de CFDI:</td>
                                    <td>{{ $invoice->cfdi_type_label }} ({{ $invoice->cfdi_type }})</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Forma de Pago:</td>
                                    <td>{{ $invoice->payment_form }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Método de Pago:</td>
                                    <td>{{ $invoice->payment_method }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Uso CFDI:</td>
                                    <td>{{ $invoice->cfdi_use }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">Fecha Creación:</td>
                                    <td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($invoice->stamped_at)
                                <tr>
                                    <td class="text-muted">Fecha Timbrado:</td>
                                    <td>{{ $invoice->stamped_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                                @if($invoice->uuid)
                                <tr>
                                    <td class="text-muted">UUID:</td>
                                    <td><code class="small">{{ $invoice->uuid }}</code></td>
                                </tr>
                                @endif
                                @if($invoice->total_payments > 1)
                                <tr>
                                    <td class="text-muted">Pago:</td>
                                    <td>{{ $invoice->payment_number }} de {{ $invoice->total_payments }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Emisor y Receptor --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Emisor</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>{{ $invoice->partnerEntity->razon_social }}</strong></p>
                            <p class="mb-1 text-muted">RFC: {{ $invoice->partnerEntity->rfc }}</p>
                            <p class="mb-1 text-muted">Régimen: {{ $invoice->partnerEntity->fiscal_regime_label }}</p>
                            <p class="mb-0 text-muted">C.P.: {{ $invoice->partnerEntity->zip_code }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Receptor</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>{{ $invoice->receptor_name }}</strong></p>
                            <p class="mb-1 text-muted">RFC: {{ $invoice->receptor_rfc }}</p>
                            @if($invoice->receptor_email)
                                <p class="mb-0 text-muted">Email: {{ $invoice->receptor_email }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Conceptos --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Conceptos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Clave</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-right">P. Unitario</th>
                                    <th class="text-right">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>
                                            <small class="text-muted">{{ $item->product_key }}</small>
                                            @if($item->sku)
                                                <br><small>{{ $item->sku }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-center">{{ number_format($item->quantity, 2) }} {{ $item->unit_name }}</td>
                                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Totales --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Totales</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td>IVA ({{ $invoice->tax_rate }}%):</td>
                            <td class="text-right">${{ number_format($invoice->tax, 2) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total:</strong></td>
                            <td class="text-right"><strong>${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Cotización relacionada --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Cotización Relacionada</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('quotes.show', $invoice->quote) }}" class="btn btn-outline-primary btn-block">
                        <i class="feather icon-file"></i> {{ $invoice->quote->quote_number }}
                    </a>
                </div>
            </div>

            {{-- Acciones de cancelación --}}
            @if($invoice->canBeCancelled())
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Cancelar Factura</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">La cancelación de facturas timbradas requiere autorización del SAT.</p>
                        <a href="{{ route('invoices.cancel-form', $invoice) }}" class="btn btn-danger btn-block">
                            <i class="feather icon-x-circle"></i> Solicitar Cancelación
                        </a>
                    </div>
                </div>
            @endif

            @if($invoice->isCancelled())
                <div class="alert alert-danger">
                    <strong>Factura Cancelada</strong>
                    <br>
                    <small>Fecha: {{ $invoice->cancelled_at?->format('d/m/Y H:i') }}</small>
                    <br>
                    <small>Motivo: {{ $invoice->cancellation_reason }}</small>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
