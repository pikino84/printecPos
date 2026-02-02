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
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Aceptada</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirada</option>
                                <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Facturada</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <input type="text"
                                   name="search"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar por número, cliente o notas..."
                                   value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary mr-2 mb-2">
                            <i class="feather icon-search"></i> Buscar
                        </button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                            Limpiar
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                                                @if($quote->status === 'draft')
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
                                                    <span class="badge badge-warning">Expirada</span>
                                                @elseif($quote->status === 'invoiced')
                                                    <span class="badge" style="background-color: #17a2b8; color: white;">Facturada</span>
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
    @if($quote->status === 'sent')
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
    @endif
@endforeach

@endsection

@section('scripts')
<script>
function openStatusSelector(quoteId, quoteNumber, currentStatus) {
    var selectHtml = '<select id="status-select" class="form-control">';

    if (currentStatus === 'sent') {
        selectHtml += '<option value="">-- Seleccionar estado --</option>';
        selectHtml += '<option value="accept">Aceptada</option>';
        selectHtml += '<option value="reject">Rechazada</option>';
        selectHtml += '<option value="expired">Expirada</option>';
    } else if (currentStatus === 'accepted') {
        selectHtml += '<option value="">-- Seleccionar estado --</option>';
        selectHtml += '<option value="invoice">Facturada</option>';
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
                'invoice': 'Facturada'
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