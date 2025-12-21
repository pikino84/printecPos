@extends('layouts.app')

@section('title', 'Mis Facturas')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-lg-6 col-md-6">
            <h4><i class="feather icon-file-text"></i> Mis Facturas</h4>
        </div>
        <div class="col-lg-6 col-md-6 text-right">
            <a href="{{ route('quotes.index') }}" class="btn btn-outline-primary">
                <i class="feather icon-arrow-left"></i> Volver a Cotizaciones
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('invoices.index') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="stamped" {{ request('status') == 'stamped' ? 'selected' : '' }}>Timbrada</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <input type="text"
                                   name="search"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar por folio, UUID o RFC..."
                                   value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="feather icon-search"></i> Buscar
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary">
                            Limpiar
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de facturas -->
    <div class="row">
        <div class="col-12">
            @if($invoices->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="feather icon-file-text" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3">No hay facturas</h5>
                        <p class="text-muted">Las facturas se generan a partir de cotizaciones aceptadas</p>
                        <a href="{{ route('quotes.index') }}" class="btn btn-primary mt-3">
                            <i class="feather icon-file"></i> Ver Cotizaciones
                        </a>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Folio</th>
                                        <th>Cotización</th>
                                        <th>Receptor</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th>UUID</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>
                                                <strong>{{ $invoice->full_folio }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $invoice->invoice_number }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('quotes.show', $invoice->quote) }}">
                                                    {{ $invoice->quote->quote_number }}
                                                </a>
                                                @if($invoice->total_payments > 1)
                                                    <br>
                                                    <small class="text-info">Pago {{ $invoice->payment_number }} de {{ $invoice->total_payments }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $invoice->receptor_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $invoice->receptor_rfc }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $invoice->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                {!! $invoice->status_badge !!}
                                            </td>
                                            <td><strong>${{ number_format($invoice->total, 2) }}</strong></td>
                                            <td>
                                                @if($invoice->uuid)
                                                    <small class="text-monospace">{{ Str::limit($invoice->uuid, 8) }}...</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('invoices.show', $invoice) }}"
                                                       class="btn btn-outline-primary"
                                                       title="Ver Detalle">
                                                        <i class="feather icon-eye"></i>
                                                    </a>

                                                    @if($invoice->isDraft())
                                                        <form action="{{ route('invoices.stamp', $invoice) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('¿Timbrar esta factura? Esta acción generará el CFDI.')">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="btn btn-success"
                                                                    title="Timbrar Factura">
                                                                <i class="feather icon-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($invoice->isStamped())
                                                        <a href="{{ route('invoices.download-xml', $invoice) }}"
                                                           class="btn btn-outline-secondary"
                                                           title="Descargar XML">
                                                            <i class="feather icon-code"></i>
                                                        </a>
                                                        <a href="{{ route('invoices.download-pdf', $invoice) }}"
                                                           class="btn btn-outline-secondary"
                                                           title="Descargar PDF">
                                                            <i class="feather icon-download"></i>
                                                        </a>
                                                    @endif

                                                    @if($invoice->canBeCancelled())
                                                        <a href="{{ route('invoices.cancel-form', $invoice) }}"
                                                           class="btn btn-outline-danger"
                                                           title="Cancelar Factura">
                                                            <i class="feather icon-x-circle"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($invoices->hasPages())
                        <div class="card-footer">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
