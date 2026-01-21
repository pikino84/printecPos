<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOwnProductRequest;
use App\Http\Requests\UpdateOwnProductRequest;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use App\Models\ProductWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OwnProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $userPartnerId = $user->partner_id;

        $query = Product::with(['productCategory', 'creator', 'variants.stocks.warehouse', 'partner'])
            ->ownProducts();

        if ($userPartnerId == 1) {
            // Printec: Ve TODOS los productos propios
        } else {
            // Asociados: Solo ven SUS productos propios
            $query->where('partner_id', $userPartnerId);
        }

        // Filtros adicionales
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('model_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('variants', function($vq) use ($search) {
                      $vq->where('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'featured':
                    $query->where('featured', true);
                    break;
            }
        }

        if ($request->filled('owner')) {
            if ($request->owner === 'own') {
                $query->where('partner_id', $userPartnerId);
            } elseif ($request->owner === 'printec' && $userPartnerId == 1) {
                $query->where('partner_id', 1);
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        // Categorías para el filtro
        if ($userPartnerId == 1) {
            $categories = ProductCategory::orderBy('name')->get();
        } else {
            $partnerIds = Partner::whereIn('type', ['proveedor', 'mixto'])
                ->pluck('id')
                ->push($userPartnerId)
                ->push(1)
                ->unique()
                ->toArray();

            $categories = ProductCategory::whereIn('partner_id', $partnerIds)
                ->orderBy('name')
                ->get();
        }

        return view('own-products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $userPartnerId = Auth::user()->partner_id;

        // Solo mostrar categorías propias del distribuidor
        $categories = ProductCategory::where('partner_id', $userPartnerId)
            ->orderBy('name')
            ->get();

        $warehouses = ProductWarehouse::where('partner_id', $userPartnerId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $hasWarehouses = $warehouses->isNotEmpty();

        return view('own-products.create', compact('categories', 'warehouses', 'hasWarehouses'));
    }

    public function store(StoreOwnProductRequest $request)
    {
        $this->authorize('create', Product::class);
        
        $product = DB::transaction(function() use ($request) {
            $partnerId = Auth::user()->partner_id;
            
            $hasWarehouses = ProductWarehouse::where('partner_id', $partnerId)
                ->where('is_active', true)
                ->exists();
                
            if (!$hasWarehouses) {
                throw new \Exception('No puedes crear productos sin almacenes configurados.');
            }
            
            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->where('partner_id', $partnerId)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $product = Product::create([
                'name' => $request->name,
                'slug' => $slug,
                'model_code' => $request->model_code,
                'price' => $request->price,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'material' => $request->material,
                'packing_type' => $request->packing_type,
                'unit_package' => $request->unit_package,
                'product_weight' => $request->product_weight,
                'product_size' => $request->product_size,
                'area_print' => $request->area_print,
                'product_category_id' => $request->product_category_id,
                'partner_id' => $partnerId,
                'owner_id' => $partnerId,
                'created_by' => Auth::id(),
                'is_active' => $request->boolean('is_active', true),
                'featured' => $request->boolean('featured'),
                'is_own_product' => true,
                'is_public' => $request->boolean('is_public') && $partnerId == 1,
            ]);

            if ($request->hasFile('main_image')) {
                $path = $request->file('main_image')->store("products/{$product->id}", 'public');
                $product->update(['main_image' => $path]);
            }

            $skuGenerated = $request->sku ?: ($product->model_code ?: 'PROD-' . $product->id);
            
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => strtoupper($skuGenerated),
                'slug' => $product->slug . '-default',
                'color_name' => 'Blanco',
                'price' => $request->price,
            ]);

            if ($request->filled('warehouse_id')) {
                ProductStock::create([
                    'variant_id' => $variant->id,
                    'warehouse_id' => $request->warehouse_id,
                    'stock' => $request->filled('initial_stock') ? (int)$request->initial_stock : 0,
                ]);
            }

            return $product;
        });

        return redirect()
            ->route('own-products.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(Product $own_product)
    {

        // Verificar que sea producto propio
        if (!$own_product->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        // Autorizar
        $this->authorize('view', $own_product);

        // Cargar relaciones
        $own_product->load([
            'productCategory',
            'variants.stocks.warehouse',
            'creator',
            'partner'
        ]);

        return view('own-products.show', compact('own_product'));
    }

    public function edit(Product $own_product)
    {
        if (!$own_product->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        $this->authorize('update', $own_product);
        $userPartnerId = Auth::user()->partner_id;

        // Cargar relaciones necesarias
        $own_product->load([
            'productCategory',
            'variants.stocks.warehouse',
            'creator',
            'partner'
        ]);

        $warehouses = ProductWarehouse::where('partner_id', $own_product->partner_id)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        // Solo mostrar categorías propias del distribuidor
        $categories = ProductCategory::where('partner_id', $userPartnerId)
            ->orderBy('name')
            ->get();

        return view('own-products.edit', compact(
            'own_product',
            'warehouses',
            'categories'
        ));
    }

    public function update(UpdateOwnProductRequest $request, Product $own_product)
    {
        if (!$own_product->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        $this->authorize('update', $own_product);

        DB::transaction(function() use ($request, $own_product) {
            $own_product->update([
                'name' => $request->name,
                'model_code' => $request->model_code,
                'price' => $request->price,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'material' => $request->material,
                'packing_type' => $request->packing_type,
                'unit_package' => $request->unit_package,
                'product_weight' => $request->product_weight,
                'product_size' => $request->product_size,
                'area_print' => $request->area_print,
                'product_category_id' => $request->product_category_id,
                'is_active' => $request->boolean('is_active', true),
                'featured' => $request->boolean('featured'),
                'is_public' => $request->boolean('is_public') && $own_product->partner_id == 1,
            ]);

            if ($request->hasFile('main_image')) {
                if ($own_product->main_image) {
                    Storage::disk('public')->delete($own_product->main_image);
                }
                
                $path = $request->file('main_image')->store("products/{$own_product->id}", 'public');
                $own_product->update(['main_image' => $path]);
            }

            if ($request->has('variants')) {
                $this->updateVariantsForProduct($own_product, $request->variants, $request);
            }
        });

        // ✅ CAMBIO: Usa 'own_product' en lugar de 'ownProduct'
        return redirect()
            ->route('own-products.show', $own_product)
            ->with('success', 'Producto actualizado correctamente');
    }

    private function updateVariantsForProduct(Product $product, array $variantsData, Request $request)
    {
        $existingVariantIds = [];
        
        foreach ($variantsData as $index => $variantData) {
            $skuExists = ProductVariant::where('sku', $variantData['sku'])
                ->where('product_id', '!=', $product->id)
                ->when(isset($variantData['id']), function($query) use ($variantData) {
                    return $query->where('id', '!=', $variantData['id']);
                })
                ->exists();
                
            if ($skuExists) {
                throw new \Exception("El SKU {$variantData['sku']} ya existe en otro producto");
            }

            if (isset($variantData['id']) && $variantData['id']) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->findOrFail($variantData['id']);
                $existingVariantIds[] = $variant->id;
                
                $variant->update([
                    'sku' => strtoupper($variantData['sku']),
                    'slug' => $variant->slug ?: Str::slug($variantData['sku']) . '-' . Str::random(4),
                    'color_name' => $variantData['color_name'],
                    'price' => $variantData['price'] ?? null,
                ]);
            } else {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper($variantData['sku']),
                    'slug' => Str::slug($variantData['sku']) . '-' . Str::random(4),
                    'color_name' => $variantData['color_name'],
                    'price' => $variantData['price'] ?? null,
                ]);
                $existingVariantIds[] = $variant->id;
            }

            if ($request->hasFile("variants.{$index}.image")) {
                if ($variant->image) {
                    Storage::disk('public')->delete($variant->image);
                }
                
                $imagePath = $request->file("variants.{$index}.image")
                    ->store("products/{$product->id}/variants", 'public');
                $variant->update(['image' => $imagePath]);
            }

            if (isset($variantData['stocks'])) {
                foreach ($variantData['stocks'] as $warehouseId => $stockAmount) {
                    ProductStock::updateOrCreate(
                        [
                            'variant_id' => $variant->id,
                            'warehouse_id' => $warehouseId,
                        ],
                        [
                            'stock' => (int) $stockAmount,
                        ]
                    );
                }
            }
        }
        
        $variantsToDelete = $product->variants()
            ->whereNotIn('id', $existingVariantIds)
            ->get();
            
        foreach ($variantsToDelete as $variant) {
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }
            
            $variant->stocks()->delete();
            $variant->delete();
        }
    }

    public function destroy(Product $own_product)
    {
        if (!$own_product->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        $this->authorize('delete', $own_product);

        DB::transaction(function() use ($own_product) {
            // Eliminar imagen
            if ($own_product->main_image) {
                Storage::disk('public')->delete($own_product->main_image);
            }

            $own_product->delete();
        });

        return redirect()
            ->route('own-products.index')
            ->with('success', 'Producto eliminado correctamente');
    }
    /**
     * Duplicar un producto propio
     */
    public function duplicate(Product $own_product)
    {
        if (!$own_product->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        $this->authorize('create', Product::class);

        try {
            $newProduct = DB::transaction(function() use ($own_product) {
                $newProduct = $own_product->replicate();
                $newProduct->name = $own_product->name . ' (Copia)';
                $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(6);
                $newProduct->model_code = $own_product->model_code ? $own_product->model_code . '-COPY' : null;
                $newProduct->created_by = Auth::id();
                $newProduct->save();

                if ($own_product->main_image && Storage::disk('public')->exists($own_product->main_image)) {
                    $extension = pathinfo($own_product->main_image, PATHINFO_EXTENSION);
                    $newImagePath = "products/{$newProduct->id}/main." . $extension;
                    Storage::disk('public')->copy($own_product->main_image, $newImagePath);
                    $newProduct->update(['main_image' => $newImagePath]);
                }

                foreach ($own_product->variants as $variant) {
                    $newVariant = $variant->replicate();
                    $newVariant->product_id = $newProduct->id;
                    $newVariant->sku = $variant->sku . '-COPY';
                    $newVariant->slug = Str::slug($newVariant->sku) . '-' . Str::random(4);
                    $newVariant->save();

                    foreach ($variant->stocks as $stock) {
                        ProductStock::create([
                            'variant_id' => $newVariant->id,
                            'warehouse_id' => $stock->warehouse_id,
                            'stock' => 0,
                        ]);
                    }
                }

                return $newProduct;
            });

            return redirect()
                ->route('own-products.edit', $newProduct)
                ->with('success', 'Producto duplicado exitosamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al duplicar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Buscar productos (API)
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = Product::with(['productCategory', 'variants'])
            ->ownProducts();

        if ($user->partner_id != 1) {
            $query->where('partner_id', $user->partner_id);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('model_code', 'like', "%{$search}%");
            });
        }

        return response()->json($query->limit(20)->get());
    }
}