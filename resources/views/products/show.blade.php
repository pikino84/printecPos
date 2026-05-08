@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Galería de imágenes -->
        <div class="col-xl-6 col-md-6">
            <div class="row">
                <!-- Thumbnails -->
                <div class="col-sm-12 col-md-3 mb-4 order-xl-1 order-md-1 order-sm-2 order-2 ">
                    <div class="swiper mySwiperThumbs">
                        <div class="swiper-wrapper">
                            @foreach ($images as $img)
                            <div class="swiper-slide">
                                <img src="{{ asset('storage/' . $img['image']) }}" alt="Thumbnail" class="img-fluid rounded shadow-sm" />
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Imagen principal -->
                <div class="col-sm-12 col-md-9  order-xl-2 order-md-2 order-sm-1 order-1">
                    <div class="swiper mySwiper2 mb-4">
                        <div class="swiper-wrapper">
                            @foreach ($images as $img)
                            <div class="swiper-slide">
                                <img src="{{ asset('storage/' . $img['image']) }}" alt="Imagen del producto" class="img-fluid rounded shadow-sm" />
                            </div>
                            @endforeach
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="row">
                <h2 class="text-3xl font-sans">{{ $producto->product_name }}</h2>
                <p class="text-muted">{{ $producto->description }}</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item">
                        <strong>Categoría:</strong> 
                        @foreach ($producto->productCategory->printecCategories as $cat)
                            {{ $cat->name }}@if (!$loop->last), @endif
                        @endforeach
                    </li>
                    <li class="list-group-item">
                        <strong>Proveedor:</strong> 
                            {{ $producto->partner->name ?? 'N/A' }}
                    </li>
                    <li class="list-group-item">
                        <strong>Nombre:</strong> 
                        {{ $producto->name ?? 'N/A' }}
                    </li>
                    <li class="list-group-item">
                        <strong>Modelo:</strong> 
                        {{ $producto->model_code ?? 'N/A' }}
                    </li>
                    @if($partnerPricing && $partnerPricing->getEffectiveTier())
                    <li class="list-group-item">
                        <strong>Tu Nivel:</strong> 
                        <span class="badge bg-primary">{{ $partnerPricing->getEffectiveTier()->name }}</span>
                        @if($partnerPricing->getEffectiveTier()->discount_percentage > 0)
                            <small class="text-success">(-{{ number_format($partnerPricing->getEffectiveTier()->discount_percentage, 0) }}% descuento)</small>
                        @endif
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Detalles del producto (la tabla de variantes) -->
        <div id="table_variant" class="col-xl-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>SKU</th>
                                    <th>Img</th>
                                    <th>Stock Total</th>
                                    <th>Color</th>
                                    @if($producto->partner->slug === 'doble-vela')
                                    <th>Status</th>
                                    @endif
                                    @if(auth()->user()->isPrintec())
                                    <th>Precio Proveedor</th>
                                    @endif
                                    <th>Tu Precio</th>
                                    <th>Precio Final</th>
                                    <th>Cantidad</th>
                                    <th>Agregar</th>
                                    @foreach($almacenesUnicos as $warehouse)
                                    <th>{{ $warehouse->nickname ?? 'Almacén' }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($producto->variants as $variant)
                                    @php
                                        // Calcular precio de costo (lo que paga el distribuidor a Printec)
                                        $precioCosto = $partnerPricing
                                            ? $partnerPricing->calculateCostPrice($variant->price, $isPrintecProduct)
                                            : $variant->price;
                                        // Calcular precio cliente final (con markup del partner)
                                        $precioClienteFinal = $partnerPricing
                                            ? $partnerPricing->calculateSalePrice($variant->price, $isPrintecProduct)
                                            : $variant->price;
                                    @endphp
                                    <tr data-sku="{{ $variant->sku }}">
                                        <td class="col_sku">{{ $variant->sku }}</td>
                                        <td class="col_img">
                                            @if($variant->image)
                                                <img src="{{ asset('storage/' . $variant->image) }}" alt="Thumbnail">
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="stock-total" data-sku="{{ $variant->sku }}">
                                            {{ number_format($variant->totalStock()) }}
                                            @if($variant->por_llegar_1 > 0)
                                                <br>
                                                <small class="text-info" title="Por llegar: {{ number_format($variant->por_llegar_1) }} pzas aprox. {{ $variant->fecha_llegada_1 ? $variant->fecha_llegada_1->format('d/m/Y') : '' }}">
                                                    +{{ number_format($variant->por_llegar_1) }}
                                                    @if($variant->fecha_llegada_1)
                                                        ({{ $variant->fecha_llegada_1->format('d/m') }})
                                                    @endif
                                                </small>
                                            @endif
                                            @if($variant->por_llegar_2 > 0)
                                                <br>
                                                <small class="text-info" title="Por llegar 2: {{ number_format($variant->por_llegar_2) }} pzas aprox. {{ $variant->fecha_llegada_2 ? $variant->fecha_llegada_2->format('d/m/Y') : '' }}">
                                                    +{{ number_format($variant->por_llegar_2) }}
                                                    @if($variant->fecha_llegada_2)
                                                        ({{ $variant->fecha_llegada_2->format('d/m') }})
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td class="wrapper_color" title="{{ $variant->color_name }}">
                                            {{ $variant->color_name ?? 'no_color' }}
                                            <div class="color-icon {{ $variant->color_name ?? 'no_color' }}" ></div>
                                        </td>
                                        @if($producto->partner->slug === 'doble-vela')
                                        <td class="text-center">
                                            @php
                                                $statusMap = [
                                                    'A'   => ['label' => 'Activo',          'class' => 'bg-success'],
                                                    'N'   => ['label' => 'Nuevo',           'class' => 'bg-primary'],
                                                    'D'   => ['label' => 'Descontinuado',   'class' => 'bg-danger'],
                                                    'NC'  => ['label' => 'Nuevo catálogo',  'class' => 'bg-primary'],
                                                    'AA'  => ['label' => 'Alta demanda',    'class' => 'bg-warning text-dark'],
                                                    'X'   => ['label' => 'Baja',            'class' => 'bg-dark'],
                                                    'P'   => ['label' => 'Próximamente',    'class' => 'bg-secondary'],
                                                ];
                                                $st = $variant->status ?? '';
                                                // Detectar ofertas (O5%, O10%, etc.)
                                                if (preg_match('/^O(\d+)%$/', $st, $m)) {
                                                    $statusInfo = ['label' => "Oferta {$m[1]}%", 'class' => 'bg-warning text-dark'];
                                                } else {
                                                    $statusInfo = $statusMap[$st] ?? ['label' => $st, 'class' => 'bg-secondary'];
                                                }
                                            @endphp
                                            @if($st)
                                                <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                                            @endif
                                        </td>
                                        @endif
                                        @if(auth()->user()->isPrintec())
                                        <td>
                                            ${{ number_format($variant->price, 2) }}
                                        </td>
                                        @endif
                                        <td>
                                            ${{ number_format($precioCosto, 2) }}
                                        </td>
                                        <td>
                                            ${{ number_format($precioClienteFinal, 2) }}
                                        </td>
                                        <td style="min-width: 130px;">
                                            @php
                                                $perMeter = $producto->isPerMeter();
                                                $stepAttr = $perMeter ? '0.01' : '1';
                                                $minAttr = $perMeter ? '0.01' : '1';
                                                $defaultQty = $perMeter ? '1.00' : '1';
                                            @endphp
                                            <div class="input-group quantity-selector">
                                                <button type="button" class="btn btn-light btn-sm btn-minus" data-variant="{{ $variant->id }}">−</button>
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    value="{{ $defaultQty }}"
                                                    min="{{ $minAttr }}"
                                                    step="{{ $stepAttr }}"
                                                    class="form-control form-control-sm text-center quantity-input"
                                                    data-variant="{{ $variant->id }}"
                                                    data-step="{{ $stepAttr }}"
                                                    style="width: 60px;"
                                                >
                                                <button type="button" class="btn btn-light btn-sm btn-plus" data-variant="{{ $variant->id }}">+</button>
                                            </div>
                                            @if($perMeter)
                                                <small class="text-muted d-block mt-1">
                                                    Por {{ $producto->unit_type === 'metro_cuadrado' ? 'm²' : 'metro lineal' }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm btn-add-to-cart"
                                                    data-variant="{{ $variant->id }}"
                                                    data-warehouse="{{ $stock->warehouse_id ?? '' }}"
                                                    data-price="{{ $precioClienteFinal }}">
                                                <i class="feather icon-shopping-cart"></i> Agregar
                                            </button>
                                        </td>
                                        @foreach($variant->stocks as $stock)
                                            <td>
                                                @if($stock->stock > 0)
                                                    <span class="text-success">{{ number_format($stock->stock) }}</span>
                                                @else
                                                    <span class="text-danger">0</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Helper: lee el valor de un input respetando decimales si su step lo permite.
    const readQty = (input) => {
        const step = parseFloat(input.getAttribute('data-step') || input.step || '1');
        return step < 1 ? parseFloat(input.value) : parseInt(input.value);
    };
    const formatQty = (value, step) => step < 1 ? value.toFixed(2) : Math.round(value).toString();

    document.querySelectorAll('.btn-minus').forEach(button => {
        button.addEventListener('click', function () {
            const variantId = this.getAttribute('data-variant');
            const input = document.querySelector(`.quantity-input[data-variant='${variantId}']`);
            const step = parseFloat(input.getAttribute('data-step') || input.step || '1');
            const min = parseFloat(input.getAttribute('min') || (step < 1 ? '0.01' : '1'));
            let value = readQty(input);
            if (isNaN(value)) value = min;
            const next = +(value - step).toFixed(2);
            if (next >= min) input.value = formatQty(next, step);
        });
    });

    document.querySelectorAll('.btn-plus').forEach(button => {
        button.addEventListener('click', function () {
            const variantId = this.getAttribute('data-variant');
            const input = document.querySelector(`.quantity-input[data-variant='${variantId}']`);
            const step = parseFloat(input.getAttribute('data-step') || input.step || '1');
            const max = parseFloat(input.getAttribute('max') || '9999');
            let value = readQty(input);
            if (isNaN(value)) value = 0;
            const next = +(value + step).toFixed(2);
            if (next <= max) input.value = formatQty(next, step);
        });
    });

    document.querySelectorAll('.btn-add-to-cart').forEach(button => {
        button.addEventListener('click', function () {
            const variantId = this.getAttribute('data-variant');
            const warehouseId = this.getAttribute('data-warehouse') || null;
            const price = this.getAttribute('data-price');
            const input = document.querySelector(`.quantity-input[data-variant='${variantId}']`);
            const quantity = readQty(input);

            if (typeof addToCart === 'function') {
                addToCart(variantId, quantity, warehouseId, price);
            }
        });
    });

});
</script>
@endsection