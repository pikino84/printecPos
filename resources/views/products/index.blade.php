@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="page-header">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h5>Catálogo de Productos</h5>
        </div>
        <div class="col-md-6 text-right">
            <select id="categoryFilter" class="form-control" style="width: auto; display: inline-block;">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <input type="text" id="searchInput" class="form-control" placeholder="Buscar..." style="width: 250px; display: inline-block;" />
        </div>
    </div>
</div>

<div class="row productGrid" id="productGrid">
    @foreach($products as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-card" data-category="{{ $product->category->slug }}">
            <div class="card">
                <img src="{{ asset('storage/' . $product->image_path) }}" class="card-img-top" alt="{{ $product->name }}" onerror="this.src='{{ asset('storage/placeholder.jpg') }}'">
                <div class="card-body">
                    <h6 class="card-title">{{ Str::limit($product->name, 35, ' ...') }}</h6>
                    <p class="card-text mb-0"><small>Categoría: {{ $product->category->name }}</small></p>
                    <p class="card-text"><small>SKU: {{ $product->sku }}</small></p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div id="loader" class="text-center my-4 productGrid" style="display: none;">
    <p>Cargando más productos...</p>
</div>
<script src="{{ asset('js/catalog.js') }}"></script>
@endsection
