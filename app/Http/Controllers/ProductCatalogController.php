<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PrintecCategory;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use App\Models\ProductWarehousesCities;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todas las categorías internas de Printec order by name
        $categories = PrintecCategory::orderBy('name')->get();

        $query = Product::with(['productCategory.printecCategories', 'variants'])
            ->whereHas('stocks', function ($q) {
                $q->where('stock', '>', 0);
            });

        // Filtro por categoría interna de Printec
        if ($request->filled('category')) {
            $query->whereHas('productCategory.printecCategories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filtro por texto de búsqueda con soporte a singular/plural
        if ($request->filled('search')) {
            $search = $request->search;

            // Versión singular simple
            $singularSearch = rtrim($search, 's');

            $query->where(function ($q) use ($search, $singularSearch) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$singularSearch}%")
                    ->orWhere('model_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$singularSearch}%")
                    ->orWhere('keywords', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$singularSearch}%")
                    ->orWhereHas('variants', function ($q2) use ($search, $singularSearch) {
                        $q2->where('code_name', 'like', "%{$search}%")
                            ->orWhere('code_name', 'like', "%{$singularSearch}%");
                    });
            });
        }
        if ($request->filled('city_id')) {
            $cityId = $request->city_id;

            $query->whereHas('stocks.warehouse', function ($q) use ($cityId) {
                $q->where('city_id', $cityId);
            });
        }

        $cities = ProductWarehousesCities::orderBy('name')->get();

        $products = $query->paginate(12);

        if ($request->ajax()) {
            return view('products.partials.cards', ['products' => $products])->render();
        }

        return view('products.index', compact('products', 'categories', 'cities'));
    }

    public function show($id)
    {
        if (!is_numeric($id)) {
            abort(404, 'Producto no encontrado');
        }

        $producto = Product::with([
            'partner', // relación con Partner (ex proveedor/asociado)
            'productCategory.printecCategories', // categorías mapeadas
            'variants.stocks.warehouse' // almacenes por variante
        ])->findOrFail($id);

        // Imagen principal
        $mainImage = [
            'image' => $producto->main_image,
            'type' => 'main',
        ];

        // Imágenes de variantes
        $variantImages = $producto->variants->map(function ($variant) {
            return [
                'image' => $variant->image,
                'type' => 'variant',
                'color' => $variant->color_name,
            ];
        })->toArray();

        $images = array_merge([$mainImage], $variantImages);

        // Almacenes únicos desde todas las variantes
        $almacenesUnicos = collect();
        foreach ($producto->variants as $variant) {
            foreach ($variant->stocks as $stock) {
                $almacenesUnicos->put($stock->warehouse_id, $stock->warehouse);
            }
        }

        return view('products.show', compact('producto', 'images', 'almacenesUnicos'));
    }

}
