<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PrintecCategory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicCatalogController extends Controller
{
    /**
     * Get the authenticated partner from the request
     */
    protected function getPartner(Request $request): Partner
    {
        return $request->attributes->get('api_partner');
    }

    /**
     * Build the product query with proper visibility filters
     */
    protected function buildProductQuery(Partner $partner)
    {
        $partnerId = $partner->id;

        $query = Product::with(['productCategory.printecCategories', 'variants.stocks.warehouse', 'partner'])
            ->where('is_active', true)
            ->whereHas('variants.stocks', function ($q) {
                $q->where('stock', '>', 0);
            });

        // Filter based on partner type (same logic as ProductCatalogController)
        if ($partnerId == 1) {
            // PRINTEC: Sees provider products + own products
            $query->where(function ($q) use ($partnerId) {
                $q->where('is_own_product', false)
                    ->orWhere(function ($subQ) use ($partnerId) {
                        $subQ->where('is_own_product', true)
                            ->where('partner_id', $partnerId);
                    });
            });
        } else {
            // ASSOCIATES: Sees provider products + public Mixto products + own products
            $query->where(function ($q) use ($partnerId) {
                $q->where('is_own_product', false)
                    ->orWhere(function ($subQ) use ($partnerId) {
                        $subQ->where('is_own_product', true)
                            ->where('partner_id', $partnerId);
                    })
                    ->orWhere(function ($subQ) {
                        $subQ->where('is_own_product', true)
                            ->where('is_public', true)
                            ->whereHas('partner', function ($partnerQuery) {
                                $partnerQuery->where('type', 'Mixto');
                            });
                    });
            });
        }

        return $query;
    }

    /**
     * Transform product for API response
     */
    protected function transformProduct(Product $product, Partner $partner): array
    {
        $showPrices = $partner->api_show_prices;

        // Get first variant price as base price
        $basePrice = $product->variants->first()?->price ?? $product->price ?? 0;

        // Build image URLs
        $images = [];
        if ($product->main_image) {
            $images[] = url(Storage::disk('public')->url($product->main_image));
        }
        foreach ($product->variants as $variant) {
            if ($variant->image) {
                $images[] = url(Storage::disk('public')->url($variant->image));
            }
        }

        // Get categories
        $categories = [];
        if ($product->productCategory && $product->productCategory->printecCategories) {
            $categories = $product->productCategory->printecCategories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ])->toArray();
        }

        // Build variants info
        $variants = $product->variants->map(function ($variant) use ($showPrices) {
            $data = [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'color' => $variant->color_name,
                'code' => $variant->code_name,
                'in_stock' => $variant->stocks->sum('stock') > 0,
            ];

            if ($showPrices) {
                $data['price'] = (float) $variant->price;
            }

            return $data;
        })->toArray();

        $data = [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'short_name' => $product->short_name,
            'description' => $product->description,
            'short_description' => $product->short_description,
            'model_code' => $product->model_code,
            'material' => $product->material,
            'images' => array_values(array_unique($images)),
            'main_image' => $images[0] ?? null,
            'categories' => $categories,
            'variants' => $variants,
            'is_featured' => (bool) $product->featured,
            'is_new' => (bool) $product->new,
            'is_own_product' => (bool) $product->is_own_product,
            'provider' => $product->partner?->name ?? null,
        ];

        if ($showPrices) {
            $data['price'] = (float) $basePrice;
        }

        return $data;
    }

    /**
     * Get product catalog list
     *
     * GET /api/public/catalog/products
     */
    public function index(Request $request)
    {
        $partner = $this->getPartner($request);
        $query = $this->buildProductQuery($partner);

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('productCategory.printecCategories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
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

        // Pagination
        $perPage = min((int) $request->input('per_page', 12), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->map(fn($product) => $this->transformProduct($product, $partner)),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get single product detail
     *
     * GET /api/public/catalog/products/{id}
     */
    public function show(Request $request, $id)
    {
        $partner = $this->getPartner($request);
        $query = $this->buildProductQuery($partner);

        $product = $query->find($id);

        if (!$product) {
            return response()->json([
                'error' => 'Product not found',
                'code' => 'PRODUCT_NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'data' => $this->transformProduct($product, $partner),
        ]);
    }

    /**
     * Get all categories
     *
     * GET /api/public/catalog/categories
     */
    public function categories(Request $request)
    {
        $partner = $this->getPartner($request);

        // Get categories that have products visible to this partner
        $query = $this->buildProductQuery($partner);
        $productIds = $query->pluck('id');

        $categories = PrintecCategory::whereHas('providerCategories.products', function ($q) use ($productIds) {
            $q->whereIn('products.id', $productIds);
        })
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
            ]);

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get partner info for widget configuration
     *
     * GET /api/public/catalog/info
     */
    public function info(Request $request)
    {
        $partner = $this->getPartner($request);

        return response()->json([
            'data' => [
                'partner_name' => $partner->name,
                'show_prices' => $partner->api_show_prices,
            ],
        ]);
    }
}
