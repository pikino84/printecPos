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
                                                ${{ number_format($item->effective_price, 2) }}
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
                                                ${{ number_format($item->item_total, 2) }}
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
                            
                            {{-- Campo oculto para client_id --}}
                            <input type="hidden" id="client_id" name="client_id" value="">

                            {{-- Información del Cliente --}}
                            <div class="mb-3">
                                <h6 class="mb-3">Información del Cliente</h6>
                                
                                {{-- Buscar cliente existente --}}
                                <div class="form-group mb-2">
                                    <label>Buscar Cliente Registrado</label>
                                    <select id="client_search" class="form-control form-control-sm" style="width: 100%;">
                                        <option></option>
                                    </select>
                                    <small class="text-muted">Busque por nombre, RFC o email</small>
                                </div>

                                <div class="text-center my-2">
                                    <small class="text-muted">- O -</small>
                                </div>

                                {{-- Email del cliente (requerido) --}}
                                <div class="form-group mb-2">
                                    <label>Email del Cliente <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           id="client_email" 
                                           name="client_email" 
                                           class="form-control form-control-sm" 
                                           placeholder="cliente@ejemplo.com">
                                </div>

                                {{-- Contenedor de info del cliente --}}
                                <div id="client_info"></div>

                                {{-- Campos adicionales para cliente nuevo --}}
                                <div id="manual_client_fields" style="display: none;">
                                    <div class="form-group mb-2">
                                        <label>Nombre del Cliente <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               id="client_name" 
                                               name="client_name" 
                                               class="form-control form-control-sm" 
                                               placeholder="Nombre completo">
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>RFC</label>
                                        <input type="text" 
                                               id="client_rfc" 
                                               name="client_rfc" 
                                               class="form-control form-control-sm" 
                                               placeholder="RFC (opcional)">
                                    </div>

                                    <div class="form-group mb-2">
                                        <label>Razón Social</label>
                                        <input type="text" 
                                               id="client_razon_social" 
                                               name="client_razon_social" 
                                               class="form-control form-control-sm" 
                                               placeholder="Razón social (opcional)">
                                    </div>
                                </div>
                            </div>

                            {{-- Notas opcionales --}}
                            <div class="form-group mb-3">
                                <label>Notas para la cotización</label>
                                <textarea name="notes" 
                                          class="form-control form-control-sm" 
                                          rows="2"
                                          placeholder="Notas o comentarios opcionales"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="feather icon-file-text"></i> Generar Cotización
                            </button>
                        </form>
                    </div>
                </div>

                <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary btn-block mt-3">
                    <i class="feather icon-arrow-left"></i> Seguir Comprando
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Botones +/-
    $('.btn-minus').on('click', function() {
        const itemId = $(this).data('item');
        const input = $(`.quantity-input[data-item="${itemId}"]`);
        let quantity = parseInt(input.val()) - 1;
        if (quantity < 1) quantity = 1;
        input.val(quantity);
        updateCartItem(itemId, quantity);
    });

    $('.btn-plus').on('click', function() {
        const itemId = $(this).data('item');
        const input = $(`.quantity-input[data-item="${itemId}"]`);
        let quantity = parseInt(input.val()) + 1;
        input.val(quantity);
        updateCartItem(itemId, quantity);
    });

    // Input manual
    $('.quantity-input').on('change', function() {
        const itemId = $(this).data('item');
        let quantity = parseInt($(this).val());
        
        if (isNaN(quantity) || quantity < 1) {
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

    // ===== SELECTOR DE CLIENTES =====
    // Inicializar Select2
    $('#client_search').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("clients.search") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return {
                    results: data.map(client => ({
                        id: client.id,
                        text: `${client.text} - ${client.rfc || 'Sin RFC'}`,
                        email: client.email,
                        telefono: client.telefono,
                        rfc: client.rfc,
                        razon_social: client.razon_social
                    }))
                };
            }
        }
    });

    // Cuando se selecciona un cliente
    $('#client_search').on('select2:select', function(e) {
        const client = e.params.data;
        
        $('#client_id').val(client.id);
        $('#client_email').val(client.email || '');
        $('#client_name').val(client.text);
        $('#client_rfc').val(client.rfc || '');
        $('#client_razon_social').val(client.razon_social || '');
        
        $('#manual_client_fields').hide();
        
        $('#client_info').html(`
            <div class="alert alert-info alert-sm">
                <strong>Cliente registrado:</strong> ${client.text}<br>
                ${client.email ? `<strong>Email:</strong> ${client.email}<br>` : ''}
                ${client.rfc ? `<strong>RFC:</strong> ${client.rfc}` : ''}
            </div>
        `);
    });

    // Cuando se limpia la selección
    $('#client_search').on('select2:clear', function() {
        $('#client_id').val('');
        $('#client_email').val('');
        $('#client_name').val('');
        $('#client_rfc').val('');
        $('#client_razon_social').val('');
        $('#client_info').html('');
        $('#manual_client_fields').hide();
    });

    // Verificar cliente cuando pierde foco el email
    $('#client_email').on('blur', function() {
        const email = $(this).val().trim();
        
        if (email && email.includes('@')) {
            $.ajax({
                url: '{{ route("clients.search") }}',
                data: { q: email },
                success: function(clients) {
                    const existingClient = clients.find(c => c.email === email);
                    
                    if (existingClient) {
                        $('#client_id').val(existingClient.id);
                        $('#client_name').val(existingClient.text);
                        $('#client_rfc').val(existingClient.rfc || '');
                        $('#client_razon_social').val(existingClient.razon_social || '');
                        
                        $('#client_info').html(`
                            <div class="alert alert-info alert-sm">
                                <strong>✓ Cliente encontrado:</strong> ${existingClient.text}
                            </div>
                        `);
                        
                        $('#manual_client_fields').hide();
                    } else {
                        $('#client_id').val('');
                        $('#manual_client_fields').show();
                        
                        $('#client_info').html(`
                            <div class="alert alert-warning alert-sm">
                                <strong>Cliente no registrado.</strong> Complete los datos adicionales.
                            </div>
                        `);
                    }
                }
            });
        }
    });
});
</script>
@endsection