@extends('layouts.app')

@section('title', $isSuperAdmin ? 'Todas las Cotizaciones' : 'Mis Cotizaciones')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-lg-6 col-md-6">
            <h4><i class="feather icon-file-text"></i> {{ $isSuperAdmin ? 'Todas las Cotizaciones' : 'Mis Cotizaciones' }}</h4>
        </div>
        <div class="col-lg-6 col-md-6 text-right">
            <a href="{{ route('cart.index') }}" class="btn btn-primary">
                <i class="feather icon-shopping-cart"></i> Ir al Carrito
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('quotes.index') }}" class="form-inline flex-wrap">
                        @if($isSuperAdmin && $partners->isNotEmpty())
                        <div class="form-group mr-2 mb-2">
                            <select name="partner_id" class="form-control form-control-sm">
                                <option value="">Todos los Partners</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                        {{ $partner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group mr-2 mb-2">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Aceptada</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirada</option>
                                <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Facturada</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pagada</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <input type="text"
                                   name="search"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar por número, cliente o notas..."
                                   value="{{ request('search') }}">
                        </div>
                        @if($isSuperAdmin)
                        <div class="form-group mr-2 mb-2">
                            <input type="date"
                                   name="date_from"
                                   class="form-control form-control-sm"
                                   value="{{ request('date_from') }}"
                                   title="Fecha desde">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <input type="date"
                                   name="date_to"
                                   class="form-control form-control-sm"
                                   value="{{ request('date_to') }}"
                                   title="Fecha hasta">
                        </div>
                        @endif
                        <button type="submit" class="btn btn-sm btn-primary mr-2 mb-2">
                            <i class="feather icon-search"></i> Buscar
                        </button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-sm btn-outline-secondary mb-2" id="btn-clear-filters">
                            Limpiar
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Super Admin -->
    @if($isSuperAdmin && $stats)
    <div class="row mb-3">
        <!-- Tarjetas resumen -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="feather icon-file-text text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Cotizaciones</h6>
                            <h4 class="mb-0">{{ number_format($stats->total_quotes) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="feather icon-dollar-sign text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Total Venta</h6>
                            <h4 class="mb-0">${{ number_format($stats->total_venta, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="feather icon-truck text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Costo Proveedor</h6>
                            <h4 class="mb-0">${{ number_format($stats->total_costo, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-light border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="feather icon-trending-up text-info" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-muted">Comision (Venta - Costo)</h6>
                            <h4 class="mb-0 {{ $stats->comision >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($stats->comision, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <!-- Tabla por Status -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="feather icon-bar-chart-2"></i> Desglose por Estado</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Estado</th>
                                    <th class="text-center">Cotizaciones</th>
                                    <th class="text-right">Total Venta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['pending','draft','sent','accepted','rejected','expired','invoiced','paid'] as $status)
                                    @if(isset($stats->by_status[$status]))
                                    <tr>
                                        <td>
                                            @php
                                                $badgeClass = match($status) {
                                                    'pending' => 'background-color: #fd7e14; color: white;',
                                                    'draft' => '',
                                                    'sent' => '',
                                                    'accepted' => '',
                                                    'rejected' => '',
                                                    'expired' => '',
                                                    'invoiced' => 'background-color: #17a2b8; color: white;',
                                                    'paid' => 'background-color: #28a745; color: white;',
                                                    default => '',
                                                };
                                                $badgeCss = match($status) {
                                                    'pending' => 'badge',
                                                    'draft' => 'badge badge-info',
                                                    'sent' => 'badge badge-primary',
                                                    'accepted' => 'badge badge-success',
                                                    'rejected' => 'badge badge-danger',
                                                    'expired' => 'badge badge-warning',
                                                    'invoiced' => 'badge',
                                                    'paid' => 'badge',
                                                    default => 'badge badge-secondary',
                                                };
                                            @endphp
                                            <span class="{{ $badgeCss }}" @if($badgeClass) style="{{ $badgeClass }}" @endif>
                                                {{ $stats->status_labels[$status] ?? $status }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ number_format($stats->by_status[$status]->count) }}</td>
                                        <td class="text-right">${{ number_format($stats->by_status[$status]->total_venta, 2) }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla por Proveedor -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="feather icon-package"></i> Desglose por Proveedor</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Proveedor</th>
                                    <th class="text-center">Cotizaciones</th>
                                    <th class="text-right">Venta</th>
                                    <th class="text-right">Costo</th>
                                    <th class="text-right">Comision</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats->by_supplier as $supplier)
                                <tr>
                                    <td><strong>{{ $supplier->supplier_name }}</strong></td>
                                    <td class="text-center">{{ number_format($supplier->total_quotes) }}</td>
                                    <td class="text-right">${{ number_format($supplier->total_venta, 2) }}</td>
                                    <td class="text-right">${{ number_format($supplier->total_costo, 2) }}</td>
                                    <td class="text-right">
                                        @php $comision = ($supplier->total_venta ?? 0) - ($supplier->total_costo ?? 0); @endphp
                                        <span class="{{ $comision >= 0 ? 'text-success' : 'text-danger' }}">
                                            ${{ number_format($comision, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Sin datos</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla por Partner (Asociado) -->
    @if($stats->by_partner->count() > 1)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="feather icon-users"></i> Desglose por Asociado</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Asociado</th>
                                    <th class="text-center">Cotizaciones</th>
                                    <th class="text-right">Total Venta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats->by_partner as $partner)
                                <tr>
                                    <td><strong>{{ $partner->partner_name }}</strong></td>
                                    <td class="text-center">{{ number_format($partner->total_quotes) }}</td>
                                    <td class="text-right">${{ number_format($partner->total_venta, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Lista de cotizaciones -->
    <div class="row">
        <div class="col-12">
            @if($quotes->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="feather icon-file-text" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3">No hay cotizaciones</h5>
                        <p class="text-muted">Crea tu primera cotización desde el carrito</p>
                        <a href="{{ route('cart.index') }}" class="btn btn-primary mt-3">
                            <i class="feather icon-shopping-cart"></i> Ir al Carrito
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
                                        <th>Número</th>
                                        <th>Cliente</th>
                                        @if($isSuperAdmin)
                                        <th>Partner</th>
                                        <th>Usuario</th>
                                        @endif
                                        <th>Descripción</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Válida hasta</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quotes as $quote)
                                        <tr>
                                            <td>
                                                <strong>{{ $quote->quote_number }}</strong>
                                                @if($quote->source === 'website')
                                                    <br><span class="badge badge-info" style="font-size: 9px;">Sitio Web</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $quote->client->nombre_completo ?? $quote->client_name ?? 'N/A' }}
                                            </td>
                                            @if($isSuperAdmin)
                                            <td>
                                                <span class="badge badge-primary">{{ $quote->partner->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $quote->user->name ?? 'N/A' }}</small>
                                            </td>
                                            @endif
                                            <td>
                                                @if($quote->short_description)
                                                    <span class="text-primary">{{ $quote->short_description }}</span>
                                                @else
                                                    <small class="text-muted">Sin descripción</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $quote->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($quote->status === 'pending')
                                                    <span class="badge" style="background-color: #fd7e14; color: white; cursor: pointer;"
                                                          onclick="openStatusSelector('{{ $quote->id }}', '{{ $quote->quote_number }}', 'pending')"
                                                          title="Clic para cambiar estado">
                                                        Pendiente <i class="feather icon-chevron-down" style="font-size: 10px;"></i>
                                                    </span>
                                                @elseif($quote->status === 'draft')
                                                    <span class="badge badge-info">Borrador</span>
                                                @elseif($quote->status === 'sent')
                                                    <span class="badge badge-primary" style="cursor: pointer;"
                                                          onclick="openStatusSelector('{{ $quote->id }}', '{{ $quote->quote_number }}', 'sent')"
                                                          title="Clic para cambiar estado">
                                                        Enviada <i class="feather icon-chevron-down" style="font-size: 10px;"></i>
                                                    </span>
                                                @elseif($quote->status === 'accepted')
                                                    <span class="badge badge-success" style="cursor: pointer;"
                                                          onclick="openStatusSelector('{{ $quote->id }}', '{{ $quote->quote_number }}', 'accepted')"
                                                          title="Clic para cambiar estado">
                                                        Aceptada <i class="feather icon-chevron-down" style="font-size: 10px;"></i>
                                                    </span>
                                                @elseif($quote->status === 'rejected')
                                                    <span class="badge badge-danger">Rechazada</span>
                                                @elseif($quote->status === 'expired')
                                                    <span class="badge badge-warning" style="cursor: pointer;"
                                                          onclick="openStatusSelector('{{ $quote->id }}', '{{ $quote->quote_number }}', 'expired')"
                                                          title="Clic para cambiar estado">
                                                        Expirada <i class="feather icon-chevron-down" style="font-size: 10px;"></i>
                                                    </span>
                                                @elseif($quote->status === 'invoiced')
                                                    <span class="badge" style="background-color: #17a2b8; color: white; cursor: pointer;"
                                                          onclick="openStatusSelector('{{ $quote->id }}', '{{ $quote->quote_number }}', 'invoiced')"
                                                          title="Clic para cambiar estado">
                                                        Facturada <i class="feather icon-chevron-down" style="font-size: 10px;"></i>
                                                    </span>
                                                @elseif($quote->status === 'paid')
                                                    <span class="badge" style="background-color: #28a745; color: white;">Pagada</span>
                                                @endif
                                            </td>
                                            <td>{{ $quote->items->count() }}</td>
                                            <td><strong>${{ number_format($quote->total, 2) }}</strong></td>
                                            <td>
                                                @if($quote->valid_until)
                                                    <small class="{{ $quote->isExpired() ? 'text-danger' : '' }}">
                                                        {{ $quote->valid_until->format('d/m/Y') }}
                                                    </small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('quotes.show', $quote) }}" 
                                                    class="btn btn-outline-primary"
                                                    title="Ver">
                                                        <i class="feather icon-eye"></i>
                                                    </a>
                                                    <a href="{{ route('quotes.pdf', $quote) }}" 
                                                    class="btn btn-outline-secondary"
                                                    title="Descargar PDF">
                                                        <i class="feather icon-download"></i>
                                                    </a>
                                                    
                                                    @if($quote->canBeEdited())
                                                        <!-- EDITAR: Solo para borradores -->
                                                        <form action="{{ route('quotes.edit-to-cart', $quote) }}" 
                                                            method="POST" 
                                                            class="d-inline"
                                                            onsubmit="return confirm('¿Editar esta cotización? Se moverá al carrito y el borrador se eliminará.')">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-outline-warning"
                                                                    title="Editar Cotización">
                                                                <i class="feather icon-edit"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <!-- CLONAR: Para cotizaciones enviadas/aceptadas/etc -->
                                                        <form action="{{ route('quotes.clone-to-cart', $quote) }}" 
                                                            method="POST" 
                                                            class="d-inline"
                                                            onsubmit="return confirm('¿Clonar esta cotización al carrito? Esto reemplazará el contenido actual del carrito.')">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-outline-info"
                                                                    title="Clonar al Carrito">
                                                                <i class="feather icon-copy"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($quote->canBeEdited())
                                                        <form action="{{ route('quotes.destroy', $quote) }}" 
                                                            method="POST" 
                                                            class="d-inline"
                                                            onsubmit="return confirm('¿Eliminar esta cotización?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-outline-danger"
                                                                    title="Eliminar">
                                                                <i class="feather icon-trash-2"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($quotes->hasPages())
                        <div class="card-footer">
                            {{ $quotes->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Formularios ocultos para cambio de estado -->
@foreach($quotes as $quote)
    @if($quote->status === 'pending')
        <form id="accept-form-{{ $quote->id }}" action="{{ route('quotes.accept', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
        <form id="reject-form-{{ $quote->id }}" action="{{ route('quotes.reject', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @elseif($quote->status === 'sent')
        <form id="accept-form-{{ $quote->id }}" action="{{ route('quotes.accept', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
        <form id="reject-form-{{ $quote->id }}" action="{{ route('quotes.reject', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
        <form id="expired-form-{{ $quote->id }}" action="{{ route('quotes.expired', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @elseif($quote->status === 'accepted')
        <form id="invoice-form-{{ $quote->id }}" action="{{ route('quotes.invoice', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @elseif($quote->status === 'expired')
        <form id="accept-form-{{ $quote->id }}" action="{{ route('quotes.accept', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @elseif($quote->status === 'invoiced')
        <form id="paid-form-{{ $quote->id }}" action="{{ route('quotes.paid', $quote) }}" method="POST" style="display: none;">
            @csrf
        </form>
    @endif
@endforeach

@endsection

@section('scripts')
<script>
// Persistencia de filtros con sessionStorage
(function() {
    var params = window.location.search;
    if (params && params !== '?') {
        sessionStorage.setItem('quotes_filters', params);
    }

    // Si no hay filtros en URL pero sí en sessionStorage, redirigir
    if (!params || params === '?') {
        var saved = sessionStorage.getItem('quotes_filters');
        if (saved) {
            window.location.replace('{{ route("quotes.index") }}' + saved);
            return;
        }
    }

    // Botón Limpiar: borrar sessionStorage
    document.getElementById('btn-clear-filters').addEventListener('click', function(e) {
        sessionStorage.removeItem('quotes_filters');
    });
})();

function openStatusSelector(quoteId, quoteNumber, currentStatus) {
    var selectHtml = '<select id="status-select" class="form-control">';

    if (currentStatus === 'pending') {
        selectHtml += '<option value="">-- Seleccionar estado --</option>';
        selectHtml += '<option value="accept">Aceptada</option>';
        selectHtml += '<option value="reject">Rechazada</option>';
    } else if (currentStatus === 'sent') {
        selectHtml += '<option value="">-- Seleccionar estado --</option>';
        selectHtml += '<option value="accept">Aceptada</option>';
        selectHtml += '<option value="reject">Rechazada</option>';
        selectHtml += '<option value="expired">Expirada</option>';
    } else if (currentStatus === 'accepted') {
        selectHtml += '<option value="">-- Seleccionar estado --</option>';
        selectHtml += '<option value="invoice">Facturada</option>';
    } else if (currentStatus === 'expired') {
        selectHtml += '<option value="">-- Seleccionar estado --</option>';
        selectHtml += '<option value="accept">Aceptada</option>';
    } else if (currentStatus === 'invoiced') {
        selectHtml += '<option value="">-- Seleccionar estado --</option>';
        selectHtml += '<option value="paid">Pagada</option>';
    }

    selectHtml += '</select>';

    swal({
        title: 'Cambiar estado',
        text: 'Cotización: ' + quoteNumber,
        content: {
            element: "div",
            attributes: {
                innerHTML: selectHtml,
            },
        },
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                closeModal: true,
            },
            confirm: {
                text: 'Cambiar',
                value: true,
                visible: true,
                closeModal: false
            }
        },
    }).then((willChange) => {
        if (willChange) {
            var selectedStatus = document.getElementById('status-select').value;

            if (!selectedStatus) {
                swal('Error', 'Debe seleccionar un estado', 'error');
                return;
            }

            var statusLabels = {
                'accept': 'Aceptada',
                'reject': 'Rechazada',
                'expired': 'Expirada',
                'invoice': 'Facturada',
                'paid': 'Pagada'
            };

            swal({
                title: '¿Confirmar cambio?',
                text: '¿Cambiar estado de "' + quoteNumber + '" a "' + statusLabels[selectedStatus] + '"?',
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: 'Cancelar',
                        value: null,
                        visible: true,
                        closeModal: true,
                    },
                    confirm: {
                        text: 'Sí, confirmar',
                        value: true,
                        visible: true,
                        closeModal: true
                    }
                },
                dangerMode: selectedStatus === 'reject' || selectedStatus === 'expired',
            }).then((confirmed) => {
                if (confirmed) {
                    document.getElementById(selectedStatus + '-form-' + quoteId).submit();
                }
            });
        }
    });
}
</script>
@endsection