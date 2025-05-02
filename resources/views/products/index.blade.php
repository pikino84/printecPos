@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4>Catálogo de Productos</h4>
        </div>
        <div class="col-md-6 text-right">
            <select id="categoryFilter" class="form-control" style="width: auto; display: inline-block;">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}">{{ $category->name }}</option>
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
