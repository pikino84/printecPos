@foreach($products as $product)
    @php
    $slugs = $product->productCategory?->printecCategories->pluck('slug')->join(',') ?? '';
    @endphp
    <div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-card" data-category="{{ $slugs }}">

        <div class="card">
            <img src="{{ asset('storage/' . $product->main_image) }}" class="card-img-top" alt="{{ $product->name }}" onerror="this.src='{{ asset('storage/placeholder.jpg') }}'">
            <div class="card-body">
                <h6 class="card-title">{{ Str::limit($product->name, 35, '...') }}</h6>
                @php
                    $categoryNames = $product->productCategory?->printecCategories->pluck('name')->join(', ') ?? 'Sin categoría';
                @endphp
                <p class="card-text mb-0"><small>Categorías: {{ $categoryNames }}</small></p>

                <p class="card-text"><small>Modelo: {{ $product->model_code }}</small></p>
            </div>
        </div>
    </div>
@endforeach
