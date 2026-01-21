@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-6 col-md-4 col-sm-12 mb-4">
            <h4>Catálogo de Productos</h4>
        </div>
        <div class="col-lg-6 col-md-8 col-sm-12 mb-4 text-right wrapper-filters">
            <select id="categoryFilter" class="form-control">
                <option value="" data-type="">Categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" data-type="{{ $category->type }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select id="cityFilter" class="form-control">
                <option value="">Ciudades</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar..."
                style="width: 250px; display: inline-block;" />
        </div>
    </div>

    <div class="row productGrid" id="productGrid">       
        @include('products.partials.cards', ['products' => $products])
    </div>

    <div id="loader" class="text-center my-4" style="display: none;">
        <p>Cargando más productos...</p>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/catalog.js') }}"></script>
@endsection
