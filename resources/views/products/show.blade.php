@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Galería de imágenes -->
        <div class="col-md-6">
            {{-- 
            @if($producto->imagenes->count())
                <div class="d-flex flex-wrap gap-2">
                    @foreach($producto->imagenes as $imagen)
                        <img src="{{ asset('storage/' . $imagen->path) }}" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: contain;">
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">Sin imágenes disponibles.</div>
            @endif
            --}}
            <div class="d-flex flex-wrap gap-2">
                <img src="{{ asset('storage/' . $producto->main_image) }}" class="img-thumbnail" style="width: auto;  object-fit: contain;">
            </div>
        </div>

        <!-- Detalles del producto -->
        <div class="col-md-6">
            <h2>{{ $producto->product_name }}</h2>
            <p class="text-muted">{{ $producto->description }}</p>
            <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item"><strong>Categoría:</strong> {{ $producto->category_name ?? 'N/A' }}</li>
                <li class="list-group-item"><strong>Proveedor:</strong> {{ $producto->provider->name ?? 'N/A' }}</li>
                <li class="list-group-item"><strong>Modelo:</strong> {{ $producto->model_code ?? 'N/A' }}</li>
                <li class="list-group-item"><strong>Stock Total:</strong> {{ $producto->stock->quantity ?? 0 }}</li>
            </ul>

            <form method="POST" action="{{-- route('cotizaciones.agregar') --}}">
                @csrf
                <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                <button class="btn btn-primary">Cotizar este producto</button>
            </form>
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
