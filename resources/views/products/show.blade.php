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
            <div class="row">
                <h2>{{ $producto->product_name }}</h2>
                <p class="text-muted">{{ $producto->description }}</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>Categoría:</strong> {{ $producto->category_name ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Proveedor:</strong> {{ $producto->provider->name ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Modelo:</strong> {{ $producto->model_code ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Stock Total:</strong> {{ $producto->stock->quantity ?? 0 }}</li>
                </ul>
            </div>
        </div>

        <!-- Detalles del producto (la tabla de variantes) -->
        <div id="table_variant" class="col-xl-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>SKU</th>
                                <th>Img</th>
                                <th>Stock</th>
                                <th>Color</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Agregar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($producto->variants as $variant)
                                <tr>
                                    <td>{{ $variant->sku }}</td>
                                    <td>
                                        @if($variant->image)
                                            <img src="{{ asset('storage/' . $variant->image) }}" alt="Thumbnail" style="width: 50px; height: 50px; object-fit: contain;">
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($variant->totalStock()) }}</td>
                                    <td>{{ $variant->color_name ?? 'N/A' }}</td>
                                    <td>${{ number_format($variant->price, 2) }}</td>
                                    <td style="width: 130px;">
                                        <div class="input-group quantity-selector">
                                            <button type="button" class="btn btn-light btn-sm btn-minus" data-variant="{{ $variant->id }}">−</button>
                                            <input 
                                                type="number" 
                                                name="quantity" 
                                                value="1" 
                                                min="1" 
                                                max="3" 
                                                class="form-control form-control-sm text-center quantity-input" 
                                                data-variant="{{ $variant->id }}" 
                                                style="width: 50px;"
                                            >
                                            <button type="button" class="btn btn-light btn-sm btn-plus" data-variant="{{ $variant->id }}">+</button>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm">Add to Cart</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{--
    <!-- Precios por volumen -->
    @if($producto->priceScales->count())
        <div class="mt-4">
            <h4>Precios por volumen</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Desde</th>
                        <th>Precio unitario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($producto->priceScales as $scale)
                        <tr>
                            <td>{{ $scale->min_quantity }} piezas</td>
                            <td>${{ number_format($scale->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Técnicas de impresión -->
    @if($producto->techniques->count())
        <div class="mt-4">
            <h4>Técnicas de impresión</h4>
            <ul class="list-group">
                @foreach($producto->techniques as $technique)
                    <li class="list-group-item">{{ $technique->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Variantes -->
    @if($producto->variants->count())
        <div class="mt-4">
            <h4>Variantes disponibles</h4>
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>SKU</th>
                        <th>Color</th>
                        <th>Talla</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($producto->variants as $variant)
                        <tr>
                            <td>{{ $variant->variant_name }}</td>
                            <td>{{ $variant->sku }}</td>
                            <td>{{ $variant->color ?? 'N/A' }}</td>
                            <td>{{ $variant->size ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Stock por almacén -->
    @if($producto->stockByWarehouse->count())
        <div class="mt-4">
            <h4>Stock por almacén</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Almacén</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($producto->stockByWarehouse as $stock)
                        <tr>
                            <td>{{ $stock->warehouse->name }}</td>
                            <td>{{ $stock->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    --}}
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-minus').forEach(button => {
        button.addEventListener('click', function () {
            const variantId = this.getAttribute('data-variant');
            const input = document.querySelector(`.quantity-input[data-variant='${variantId}']`);
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });
    });

    document.querySelectorAll('.btn-plus').forEach(button => {
        button.addEventListener('click', function () {
            const variantId = this.getAttribute('data-variant');
            const input = document.querySelector(`.quantity-input[data-variant='${variantId}']`);
            let value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max'));
            if (value < max) {
                input.value = value + 1;
            }
        });
    });
});
</script>
@endsection
