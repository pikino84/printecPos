<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PrintecCategory;
use App\Models\ProductVariant;
use App\Models\ProductStock;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todas las categorías internas de Printec order by name
        $categories = PrintecCategory::orderBy('name')->get();

        $query = Product::with(['productCategory.printecCategories', 'variants']);

        // Filtro por categoría interna de Printec
        if ($request->filled('category')) {
            $query->whereHas('productCategory.printecCategories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filtro por texto de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('model_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%")
                  ->orWhereHas('variants', function ($q2) use ($search) {
                      $q2->where('code_name', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->paginate(12);

        if ($request->ajax()) {
            return view('products.partials.cards', ['products' => $products])->render();
        }

        return view('products.index', compact('products', 'categories'));
    }

    public function show($id)
    {
        
        // Mostrar un producto específico con sus imágenes y categoría
        // Asegúrate de que el ID sea válido y el producto exista
        if (!is_numeric($id)) {
            abort(404, 'Producto no encontrado');
        }
        
        $producto = Product::join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->join('product_providers', 'products.product_provider_id', '=', 'product_providers.id')
            ->join('printec_category_product_category', 'products.product_category_id', '=', 'printec_category_product_category.product_category_id')
            ->join('printec_categories', 'printec_category_product_category.printec_category_id', '=', 'printec_categories.id')
            ->with(['productCategory.printecCategories', 'variants'])
            ->select([
                'products.*',
                'products.name as product_name',
                'printec_categories.name as category_name',
                
            ])
            ->findOrFail($id);

        // Saca la imagen principal
        $mainImage = [
            'image' => $producto->main_image,
            'type' => 'main' // Opcional, por si quieres saber que esta es la principal
        ];
        // Saca las imágenes de las variantes
        $variantImages = ProductVariant::where('product_id', $id)
        ->get(['image', 'color_name']) // Traemos ambos campos
        ->map(function ($variant) {
            return [
                'image' => $variant->image,
                'type' => 'variant',
                'color' => $variant->color_name, // Ahora sí tienes el color
            ];
        })->toArray();

        // Combina en un solo array
        $images = array_merge([$mainImage], $variantImages);

        return view('products.show', compact('producto', 'images'));
    }

}
