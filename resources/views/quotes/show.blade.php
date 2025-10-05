@extends('layouts.app')

@section('title', 'Cotización ' . $quote->quote_number)

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h4>
                <i class="feather icon-file-text"></i> 
                Cotización {{ $quote->quote_number }}
            </h4>
        </div>
        <div class="col-lg-4 text-right">
            <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="feather icon-arrow-left"></i> Volver
            </a>
            <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-primary btn-sm">
                <i class="feather icon-download"></i> Descargar PDF
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información de la cotización -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Detalles de la Cotización</h5>
                    <div class="card-header-right">
                        @if($quote->status === 'draft')
                            <span class="badge badge-secondary">Borrador</span>
                        @elseif($quote->status === 'sent')
                            <span class="badge badge-primary">Enviada</span>
                        @elseif($quote->status === 'accepted')
                            <span class="badge badge-success">Aceptada</span>
                        @elseif($quote->status === 'rejected')
                            <span class="badge badge-danger">Rechazada</span>
                        @elseif($quote->status === 'expired')
                            <span class="badge badge-warning">Expirada</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Fecha:</strong> {{ $quote->created_at->format('d/m/Y H:i') }}</p>
                            <p class="mb-1"><strong>Válida hasta:</strong> 
                                @if($quote->valid_until)
                                    {{ $quote->valid_until->format('d/m/Y') }}
                                    @if($quote->isExpired())
                                        <span class="text-danger">(Expirada)</span>
                                    @endif
                                @else
                                    <span class="text-muted">No definida</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            @if($quote->sent_at)
                                <p class="mb-1"><strong>Enviada:</strong> {{ $quote->sent_at->format('d/m/Y H:i') }}</p>
                                <p class="mb-1"><strong>Enviada a:</strong> {{ $quote->sent_to_email }}</p>
                            @endif
                        </div>
                    </div>

                    @if($quote->notes)
                        <div class="alert alert-info">
                            <strong>Notas internas:</strong><br>
                            {{ $quote->notes }}
                        </div>
                    @endif

                    @if($quote->customer_notes)
                        <div class="alert alert-secondary">
                            <strong>Comentarios para el cliente:</strong><br>
                            {{ $quote->customer_notes }}
                        </div>
                    @endif

                    <!-- Items -->
                    <h6 class="mt-4 mb-3">Productos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 60px;">Img</th>
                                    <th>Producto</th>
                                    <th>SKU</th>
                                    <th>Almacén</th>
                                    <th style="width: 80px;">Cant.</th>
                                    <th style="width: 100px;">P. Unit.</th>
                                    <th style="width: 100px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quote->items as $item)
                                    <tr>
                                        <td>
                                            <img src="{{ asset('storage/' . ($item->variant->image ?? $item->product->main_image)) }}" 
                                                 class="img-fluid rounded"
                                                 style="max-width: 50px;">
                                        </td>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->variant->color_name)
                                                <br><small class="text-muted">{{ $item->variant->color_name }}</small>
                                            @endif
                                        </td>
                                        <td><small>{{ $item->variant->sku }}</small></td>
                                        <td>
                                            @if($item->warehouse)
                                                <small>{{ $item->warehouse->nickname ?? $item->warehouse->name }}</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right"><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-right"><strong>Subtotal:</strong></td>
                                    <td class="text-right"><strong>${{ number_format($quote->subtotal, 2) }}</strong></td>
                                </tr>
                                @if($quote->tax > 0)
                                    <tr>
                                        <td colspan="6" class="text-right">IVA:</td>
                                        <td class="text-right">${{ number_format($quote->tax, 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="table-active">
                                    <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                                    <td class="text-right"><strong class="text-primary">${{ number_format($quote->total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="col-lg-4">
            @if($quote->canBeSent())
                <div class="card">
                    <div class="card-header">
                        <h5>Enviar Cotización</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('quotes.send', $quote) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Email del cliente *</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control form-control-sm" 
                                       required
                                       placeholder="cliente@ejemplo.com">
                            </div>
                            <div class="form-group">
                                <label>Mensaje (opcional)</label>
                                <textarea name="message" 
                                          class="form-control form-control-sm" 
                                          rows="3"
                                          placeholder="Mensaje adicional para el cliente..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="feather icon-send"></i> Enviar por Email
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Información</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Creada por:</strong><br>
                        {{ $quote->user->name }}
                    </p>
                    <p class="mb-2">
                        <strong>Partner:</strong><br>
                        {{ $quote->partner->name }}
                    </p>
                    <p class="mb-0">
                        <strong>Total de items:</strong><br>
                        {{ $quote->items->sum('quantity') }} productos
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection