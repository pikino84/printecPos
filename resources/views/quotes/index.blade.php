@extends('layouts.app')

@section('title', 'Mis Cotizaciones')

@section('content')
<style>
    /* Fix para que los modales queden por encima del sidebar y backdrop */
    .modal {
        z-index: 1060 !important;
    }
    .modal-backdrop {
        z-index: 1055 !important;
    }
</style>
<div class="container">
    <div class="row mb-4">
        <div class="col-lg-6 col-md-6">
            <h4><i class="feather icon-file-text"></i> Mis Cotizaciones</h4>
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
                    <form method="GET" action="{{ route('quotes.index') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Aceptada</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirada</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <input type="text"
                                   name="search"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar por número o notas..."
                                   value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary mr-2">
                            <i class="feather icon-search"></i> Buscar
                        </button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                                    <span class="badge badge-info ">Borrador</span>
                                                @elseif($quote->status === 'sent')
                                                    <span class="badge badge-primary">Enviada</span>
                                                @elseif($quote->status === 'accepted')
                                                    <span class="badge badge-success">Aceptada</span>
                                                @elseif($quote->status === 'rejected')
                                                    <span class="badge badge-danger">Rechazada</span>
                                                @elseif($quote->status === 'expired')
                                                    <span class="badge badge-warning">Expirada</span>
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

                                                    {{-- ACEPTAR: Solo para cotizaciones enviadas --}}
                                                    @if($quote->canBeAccepted())
                                                        <button type="button"
                                                                class="btn btn-outline-success"
                                                                title="Aceptar Cotización"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#acceptModal{{ $quote->id }}">
                                                            <i class="feather icon-check-circle"></i>
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-outline-danger"
                                                                title="Rechazar Cotización"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rejectModal{{ $quote->id }}">
                                                            <i class="feather icon-x-circle"></i>
                                                        </button>
                                                    @endif

                                                    {{-- FACTURAR: Solo para cotizaciones aceptadas --}}
                                                    @if($quote->canGenerateInvoices() && !$quote->isFullyInvoiced())
                                                        <a href="{{ route('invoices.create-from-quote', $quote) }}"
                                                           class="btn btn-success"
                                                           title="Generar Factura(s)">
                                                            <i class="feather icon-file-plus"></i>
                                                        </a>
                                                    @endif

                                                    {{-- VER FACTURAS: Si ya tiene facturas --}}
                                                    @if($quote->hasInvoices())
                                                        <a href="{{ route('invoices.index', ['quote_id' => $quote->id]) }}"
                                                           class="btn btn-outline-success"
                                                           title="Ver Facturas">
                                                            <i class="feather icon-file-text"></i>
                                                        </a>
                                                    @endif

                                                    @if($quote->canBeEdited())
                                                        <!-- EDITAR: Solo para borradores -->
                                                        <form action="{{ route('quotes.edit-to-cart', $quote) }}"
                                                            method="POST"
                                                            class="d-inline"
                                                            id="editForm{{ $quote->id }}">
                                                            @csrf
                                                            <button type="button"
                                                                    class="btn btn-outline-warning"
                                                                    title="Editar Cotización"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editModal{{ $quote->id }}">
                                                                <i class="feather icon-edit"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <!-- CLONAR: Para cotizaciones enviadas/aceptadas/etc -->
                                                        <form action="{{ route('quotes.clone-to-cart', $quote) }}"
                                                            method="POST"
                                                            class="d-inline"
                                                            id="cloneForm{{ $quote->id }}">
                                                            @csrf
                                                            <button type="button"
                                                                    class="btn btn-outline-info"
                                                                    title="Clonar al Carrito"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#cloneModal{{ $quote->id }}">
                                                                <i class="feather icon-copy"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($quote->canBeEdited())
                                                        <form action="{{ route('quotes.destroy', $quote) }}"
                                                            method="POST"
                                                            class="d-inline"
                                                            id="deleteForm{{ $quote->id }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                    class="btn btn-outline-danger"
                                                                    title="Eliminar"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteModal{{ $quote->id }}">
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

{{-- MODALES --}}
@foreach($quotes as $quote)
    {{-- Modal Aceptar Cotización --}}
    @if($quote->canBeAccepted())
    <div class="modal fade" id="acceptModal{{ $quote->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="feather icon-check-circle"></i> Aceptar Cotización
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de aceptar la cotización <strong>{{ $quote->quote_number }}</strong>?</p>
                    <div class="alert alert-info">
                        <i class="feather icon-info"></i>
                        Al aceptar esta cotización podrá generar las facturas correspondientes.
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td>Total:</td>
                            <td class="text-right"><strong>${{ number_format($quote->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Items:</td>
                            <td class="text-right">{{ $quote->items->count() }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('quotes.accept', $quote) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="feather icon-check"></i> Aceptar Cotización
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Rechazar Cotización --}}
    <div class="modal fade" id="rejectModal{{ $quote->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="feather icon-x-circle"></i> Rechazar Cotización
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de rechazar la cotización <strong>{{ $quote->quote_number }}</strong>?</p>
                    <div class="alert alert-warning">
                        <i class="feather icon-alert-triangle"></i>
                        Esta acción marcará la cotización como rechazada. El cliente será notificado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('quotes.reject', $quote) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="feather icon-x"></i> Rechazar Cotización
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Clonar al Carrito --}}
    @if(!$quote->canBeEdited())
    <div class="modal fade" id="cloneModal{{ $quote->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="feather icon-copy"></i> Clonar al Carrito
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Desea clonar la cotización <strong>{{ $quote->quote_number }}</strong> al carrito?</p>
                    <div class="alert alert-warning">
                        <i class="feather icon-alert-triangle"></i>
                        <strong>Atención:</strong> Esto reemplazará el contenido actual del carrito.
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td>Items a clonar:</td>
                            <td class="text-right"><strong>{{ $quote->items->count() }}</strong></td>
                        </tr>
                        <tr>
                            <td>Total:</td>
                            <td class="text-right"><strong>${{ number_format($quote->total, 2) }}</strong></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" onclick="document.getElementById('cloneForm{{ $quote->id }}').submit();">
                        <i class="feather icon-copy"></i> Clonar al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Editar Cotización --}}
    @if($quote->canBeEdited())
    <div class="modal fade" id="editModal{{ $quote->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="feather icon-edit"></i> Editar Cotización
                    </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Desea editar la cotización <strong>{{ $quote->quote_number }}</strong>?</p>
                    <div class="alert alert-warning">
                        <i class="feather icon-alert-triangle"></i>
                        <strong>Atención:</strong> La cotización se moverá al carrito y el borrador actual se eliminará.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="document.getElementById('editForm{{ $quote->id }}').submit();">
                        <i class="feather icon-edit"></i> Editar Cotización
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Eliminar Cotización --}}
    <div class="modal fade" id="deleteModal{{ $quote->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="feather icon-trash-2"></i> Eliminar Cotización
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de eliminar la cotización <strong>{{ $quote->quote_number }}</strong>?</p>
                    <div class="alert alert-danger">
                        <i class="feather icon-alert-circle"></i>
                        <strong>Esta acción no se puede deshacer.</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteForm{{ $quote->id }}').submit();">
                        <i class="feather icon-trash-2"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection
