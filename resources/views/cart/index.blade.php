@extends('layouts.app')

@section('title', 'Mi Carrito')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h4>
                <i class="feather icon-shopping-cart"></i> Mi Carrito
            </h4>
        </div>
    </div>

    @if($cartItems->isEmpty())
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="feather icon-shopping-cart" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3">Tu carrito está vacío</h5>
                        <p class="text-muted">Agrega productos desde el catálogo para generar cotizaciones</p>
                        <a href="{{ route('catalogo.index') }}" class="btn btn-primary mt-3">
                            <i class="feather icon-arrow-left"></i> Ir al Catálogo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <!-- Items del carrito -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Productos ({{ $cartItems->count() }} items)</h5>
                        <div class="card-header-right">
                            <form action="{{ route('cart.clear') }}" method="POST" 
                                  onsubmit="return confirm('¿Vaciar todo el carrito?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="feather icon-trash-2"></i> Vaciar Carrito
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 80px;">Imagen</th>
                                        <th>Producto</th>
                                        <th>SKU</th>
                                        <th>Almacén</th>
                                        <th style="width: 100px;">Precio</th>
                                        <th style="width: 140px;">Cantidad</th>
                                        <th style="width: 100px;">Subtotal</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartItems as $item)
                                        @php
                                            $price = $item->variant->price ?? $item->product->price;
                                            $itemTotal = $item->quantity * $price;
                                        @endphp
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <img src="{{ asset('storage/' . ($item->variant->image ?? $item->product->main_image)) }}" 
                                                     class="img-fluid rounded" 
                                                     style="max-width: 60px;"
                                                     alt="{{ $item->product->name }}">
                                            </td>
                                            <td>
                                                <strong>{{ $item->product->name }}</strong>
                                                @if($item->variant->color_name)
                                                    <br><small class="text-muted">Color: {{ $item->variant->color_name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $item->variant->sku }}</small>
                                            </td>
                                            <td>
                                                @if($item->warehouse)
                                                    <small>{{ $item->warehouse->nickname ?? $item->warehouse->name }}</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                ${{ number_format($price, 2) }}
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary btn-minus" 
                                                            data-item="{{ $item->id }}">-</button>
                                                    <input type="number" 
                                                           class="form-control text-center quantity-input" 
                                                           value="{{ $item->quantity }}"
                                                           min="1"
                                                           data-item="{{ $item->id }}"
                                                           style="max-width: 60px;">
                                                    <button type="button" class="btn btn-outline-secondary btn-plus"
                                                            data-item="{{ $item->id }}">+</button>
                                                </div>
                                            </td>
                                            <td class="item-subtotal">
                                                ${{ number_format($itemTotal, 2) }}
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger btn-remove"
                                                        data-item="{{ $item->id }}">
                                                    <i class="feather icon-trash-2"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen y Cotización -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Resumen</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="cart-subtotal">${{ number_format($subtotal, 2) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-primary" id="cart-total">${{ number_format($subtotal, 2) }}</strong>
                        </div>

                        <form action="{{ route('quotes.create') }}" method="POST">
                            @csrf
                            
                            <div class="form-group">
                                <label>Descripción corta (opcional)</label>
                                <input type="text" 
                                    name="short_description" 
                                    class="form-control form-control-sm" 
                                    maxlength="255"
                                    placeholder="Ej: Campaña Navidad 2025">
                                <small class="text-muted">Máximo 255 caracteres</small>
                            </div>

                            <div class="form-group">
                                <label>Notas internas (opcional)</label>
                                <textarea name="notes" 
                                        class="form-control form-control-sm" 
                                        rows="2"
                                        placeholder="Notas para uso interno..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Comentarios para el cliente (opcional)</label>
                                <textarea name="customer_notes" 
                                        class="form-control form-control-sm" 
                                        rows="2"
                                        placeholder="Comentarios que verá el cliente..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Válida por (días)</label>
                                <input type="number" 
                                    name="valid_days" 
                                    class="form-control form-control-sm" 
                                    value="15"
                                    min="1"
                                    max="90">
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="feather icon-file-text"></i> Generar Cotización
                            </button>
                        </form>
                        

                        <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary btn-block mt-2">
                            <i class="feather icon-arrow-left"></i> Continuar Comprando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Actualizar cantidad
    $('.btn-plus, .btn-minus').on('click', function() {
        const itemId = $(this).data('item');
        const input = $(`.quantity-input[data-item="${itemId}"]`);
        let quantity = parseInt(input.val());
        
        if ($(this).hasClass('btn-plus')) {
            quantity++;
        } else if (quantity > 1) {
            quantity--;
        }
        
        input.val(quantity);
        updateCartItem(itemId, quantity);
    });

    $('.quantity-input').on('change', function() {
        const itemId = $(this).data('item');
        const quantity = parseInt($(this).val());
        
        if (quantity < 1) {
            $(this).val(1);
            return;
        }
        
        updateCartItem(itemId, quantity);
    });

    // Eliminar item
    $('.btn-remove').on('click', function() {
        if (!confirm('¿Eliminar este producto del carrito?')) return;
        
        const itemId = $(this).data('item');
        const url = `/cart/${itemId}`;
        
        $.ajax({
            url: url,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $(`tr[data-item-id="${itemId}"]`).fadeOut(300, function() {
                    $(this).remove();
                    updateCartTotals(response.cart_total);
                    updateCartBadge(response.cart_count);
                    
                    // Si el carrito quedó vacío, recargar
                    if (response.cart_count === 0) {
                        location.reload();
                    }
                });
            },
            error: function(xhr) {
                alert('Error al eliminar el producto');
            }
        });
    });

    function updateCartItem(itemId, quantity) {
        $.ajax({
            url: `/cart/${itemId}`,
            type: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { quantity: quantity },
            success: function(response) {
                // Actualizar subtotal del item
                $(`tr[data-item-id="${itemId}"] .item-subtotal`).text('$' + response.item_total);
                // Actualizar totales
                updateCartTotals(response.cart_total);
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert(xhr.responseJSON.message);
                }
            }
        });
    }

    function updateCartTotals(total) {
        $('#cart-subtotal').text('$' + total);
        $('#cart-total').text('$' + total);
    }

    function updateCartBadge(count) {
        $('.cart-badge').text(count);
    }
});
</script>
@endsection