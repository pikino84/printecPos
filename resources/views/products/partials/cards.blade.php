@foreach($products as $product)
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-card" data-category="{{ $product->category->slug }}">
        <div class="card">
            <img src="{{ asset('storage/' . $product->image_path) }}" class="card-img-top" alt="{{ $product->name }}" onerror="this.src='{{ asset('storage/placeholder.jpg') }}'">
            <div class="card-body">
                <h6 class="card-title">{{ Str::limit($product->name, 35, '...') }}</h6>
                <p class="card-text mb-0"><small>Categoría: {{ $product->category->name }}</small></p>
                <p class="card-text"><small>SKU: {{ $product->sku }}</small></p>
            </div>
        </div>
    </div>
@endforeach