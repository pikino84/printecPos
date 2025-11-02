<?php

// ============================================================================
// CONTROLADOR: OWN PRODUCTS
// ============================================================================

// app/Http/Controllers/OwnProductController.php
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

class OwnProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage-own-products|view-own-products');
    }

    public function index(Request $request)
    {
        $query = Product::with(['productCategory', 'creator', 'variants.stocks.warehouse', 'partner'])
            ->ownProducts()
            ->visibleFor(Auth::user());

        // Filtros
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
                $query->where('partner_id', Auth::user()->partner_id);
            } elseif ($request->owner === 'printec') {
                $query->where('partner_id', 1)->where('is_public', true);
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        // Categorías para el filtro (solo las que tienen productos propios)
        $categories = ProductCategory::where('partner_id', Auth::user()->partner_id)
            ->orderBy('name')
            ->get();

        return view('own-products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);
        
        $userPartnerId = Auth::user()->partner_id;
        
        // Categorías del partner actual
        $categories = ProductCategory::where('partner_id', $userPartnerId)
            ->orderBy('name')
            ->get();
        
        // Partners que pueden crear productos propios (Asociado y Mixto)
        $partners = Partner::whereIn('type', ['Asociado', 'Mixto'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Almacenes del partner actual
        $warehouses = ProductWarehouse::where('partner_id', $userPartnerId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // ⚠️ NUEVO: Validar que el partner tenga almacenes
        $hasWarehouses = $warehouses->isNotEmpty();

        return view('own-products.create', compact('categories', 'warehouses', 'partners', 'hasWarehouses'));
    }

    public function store(StoreOwnProductRequest $request)
    {
        $this->authorize('create', Product::class);

        DB::transaction(function() use ($request) {
            $partnerId = Auth::user()->partner_id;
            
            //Valida que el partner tenga al menos un almacén
            $hasWarehouses = ProductWarehouse::where('partner_id', $partnerId)
                ->where('is_active', true)
                ->exists();
                
            if (!$hasWarehouses) {
                throw new \Exception('No puedes crear productos sin almacenes configurados. Contacta al administrador o crea un almacén primero.');
            }
            
            // Crear slug único
            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->where('partner_id', $partnerId)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Crear producto propio
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

            // Imagen principal
            if ($request->hasFile('main_image')) {
                $path = $request->file('main_image')->store("products/{$product->id}", 'public');
                $product->update(['main_image' => $path]);
            }

            // Crear variante principal
            $skuGenerated = $request->sku ?: ($product->model_code ?: 'PROD-' . $product->id);
            
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => strtoupper($skuGenerated),
                'slug' => $product->slug . '-default',
                'color_name' => 'Único',
                'price' => null, // Usa precio del producto
            ]);

            //MODIFICADO: Stock inicial - SIEMPRE crear registro, aunque sea en 0
            // Si el partner tiene almacén seleccionado, usar ese. Si no, usar el primero disponible
            if ($request->filled('warehouse_id')) {
                ProductStock::create([
                    'variant_id' => $variant->id,
                    'warehouse_id' => $request->warehouse_id,
                    'stock' => $request->filled('initial_stock') ? (int)$request->initial_stock : 0,
                ]);
            } else {
                // Si por alguna razón no hay warehouse_id, usar el primer almacén del partner
                $firstWarehouse = ProductWarehouse::where('partner_id', $partnerId)
                    ->where('is_active', true)
                    ->first();
                    
                if ($firstWarehouse) {
                    ProductStock::create([
                        'variant_id' => $variant->id,
                        'warehouse_id' => $firstWarehouse->id,
                        'stock' => 0,
                    ]);
                }
            }

            return $product;
        });

        return redirect()->route('own-products.index')
            ->with('success', 'Producto propio creado exitosamente.');
    }

    public function show(Product $ownProduct)
    {
        // Verificar que sea producto propio
        if (!$ownProduct->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        $this->authorize('view', $ownProduct);
        
        $ownProduct->load([
            'productCategory', 
            'creator', 
            'partner',
            'variants.stocks.warehouse'
        ]);

        return view('own-products.show', compact('ownProduct'));
    }

    public function edit(Product $ownProduct)
    {
    $owner = Auth::user();
        // Verificar que sea producto propio
        if (!$ownProduct->is_own_product) {
            abort(404, 'Producto no encontrado');
        }
        
        $this->authorize('update', $ownProduct);
        
        // Cargar relaciones completas
        $ownProduct->load(['variants.stocks.warehouse']);
        
        $partners = Partner::where('is_active', true)->orderBy('name')->get();
        
        $categories = ProductCategory::where('partner_id', Auth::user()->partner_id)
            ->orderBy('name')
            ->get();

        $warehouses = ProductWarehouse::where('partner_id', Auth::user()->partner_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('own-products.edit', compact('ownProduct', 'categories', 'warehouses', 'partners'));
    }

    public function update(Request $request, Product $ownProduct)
    {
        // Verificar que sea producto propio
        if (!$ownProduct->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        $this->authorize('update', $ownProduct);

        // Validación básica del producto
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model_code' => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'packing_type' => 'nullable|string|max:255',
            'unit_package' => 'nullable|string|max:255',
            'product_weight' => 'nullable|string|max:255',
            'product_size' => 'nullable|string|max:255',
            'area_print' => 'nullable|string|max:255',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'is_active' => 'boolean',
            'featured' => 'boolean',
            'is_public' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            
            // Validación de variantes
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.sku' => 'required_with:variants|string|max:100',
            'variants.*.color_name' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'variants.*.stocks' => 'nullable|array',
            'variants.*.stocks.*' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $request, $ownProduct) {
            // Actualizar información básica del producto
            $ownProduct->update([
                'name' => $validated['name'],
                'model_code' => $validated['model_code'],
                'short_description' => $validated['short_description'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'material' => $validated['material'],
                'packing_type' => $validated['packing_type'],
                'unit_package' => $validated['unit_package'],
                'product_weight' => $validated['product_weight'],
                'product_size' => $validated['product_size'],
                'area_print' => $validated['area_print'],
                'product_category_id' => $validated['product_category_id'],
                'is_active' => $request->boolean('is_active'),
                'featured' => $request->boolean('featured'),
                'is_public' => $request->boolean('is_public') && auth()->user()->partner_id == 1,
            ]);

            // Manejar imagen principal
            if ($request->hasFile('main_image')) {
                // Eliminar imagen anterior si existe
                if ($ownProduct->main_image) {
                    Storage::disk('public')->delete($ownProduct->main_image);
                }
                
                $imagePath = $request->file('main_image')->store("products/{$ownProduct->id}", 'public');
                $ownProduct->update(['main_image' => $imagePath]);
            }

            // Procesar variantes si existen
            if ($request->has('variants')) {
                $this->updateVariantsForProduct($ownProduct, $request->input('variants'), $request);
            }
        });

        return redirect()
            ->route('own-products.show', $ownProduct)
            ->with('success', 'Producto actualizado correctamente');
    }

    private function updateVariantsForProduct(Product $product, array $variantsData, Request $request)
    {
        $existingVariantIds = [];
        
        foreach ($variantsData as $index => $variantData) {
            // Verificar que el SKU sea único
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
                // Actualizar variante existente
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
                // Crear nueva variante
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper($variantData['sku']),
                    'slug' => Str::slug($variantData['sku']) . '-' . Str::random(4),
                    'color_name' => $variantData['color_name'],
                    'price' => $variantData['price'] ?? null,
                ]);
                $existingVariantIds[] = $variant->id;
            }

            // Actualizar imagen de la variante
            if ($request->hasFile("variants.{$index}.image")) {
                // Eliminar imagen anterior si existe
                if ($variant->image) {
                    Storage::disk('public')->delete($variant->image);
                }
                
                $imagePath = $request->file("variants.{$index}.image")
                    ->store("products/{$product->id}/variants", 'public');
                $variant->update(['image' => $imagePath]);
            }

            // Actualizar stock por almacén
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
        
        // Eliminar variantes que ya no están en el array
        $variantsToDelete = $product->variants()
            ->whereNotIn('id', $existingVariantIds)
            ->get();
            
        foreach ($variantsToDelete as $variant) {
            // Eliminar imagen si existe
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }
            
            // Eliminar stocks asociados
            $variant->stocks()->delete();
            
            // Eliminar la variante
            $variant->delete();
        }
    }

    public function destroy(Product $ownProduct)
    {
        // Verificar que sea producto propio
        if (!$ownProduct->is_own_product) {
            abort(404, 'Producto no encontrado');
        }

        $this->authorize('delete', $ownProduct);

        // TODO: Verificar que no tenga ventas o cotizaciones activas
        
        // Eliminar imagen principal
        if ($ownProduct->main_image) {
            Storage::disk('public')->delete($ownProduct->main_image);
        }

        $ownProduct->delete();

        return redirect()->route('own-products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }

    // ========================================================================
    // MÉTODOS ADICIONALES
    // ========================================================================

    // Endpoint para búsqueda AJAX
    public function search(Request $request)
    {
        $query = Product::ownProducts()
            ->visibleFor(Auth::user())
            ->where('is_active', true);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('model_code', 'like', "%{$search}%")
                  ->orWhereHas('variants', function($vq) use ($search) {
                      $vq->where('sku', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->with('variants')
            ->limit(10)
            ->get(['id', 'name', 'model_code', 'price', 'main_image']);

        return response()->json($products);
    }

    // Ver variantes de un producto
    public function variants(Product $ownProduct)
    {
        if (!$ownProduct->is_own_product) {
            abort(404);
        }

        $this->authorize('view', $ownProduct);

        $variants = $ownProduct->variants()
            ->with(['stocks.warehouse'])
            ->get();

        return view('own-products.variants', compact('ownProduct', 'variants'));
    }

    // Duplicar producto
    public function duplicate(Product $ownProduct)
    {
        if (!$ownProduct->is_own_product) {
            abort(404);
        }

        $this->authorize('create', Product::class);

        DB::transaction(function() use ($ownProduct) {
            $newProduct = $ownProduct->replicate();
            $newProduct->name = $newProduct->name . ' (Copia)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(4);
            $newProduct->model_code = $newProduct->model_code ? $newProduct->model_code . '-COPY' : null;
            $newProduct->created_by = Auth::id();
            $newProduct->save();

            // Duplicar variantes
            foreach ($ownProduct->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->sku = $newVariant->sku . '-COPY';
                $newVariant->slug = Str::slug($newVariant->sku) . '-' . Str::random(4);
                $newVariant->save();

                // Duplicar stocks (en 0)
                foreach ($variant->stocks as $stock) {
                    ProductStock::create([
                        'variant_id' => $newVariant->id,
                        'warehouse_id' => $stock->warehouse_id,
                        'stock' => 0, // Stock inicial en 0
                    ]);
                }
            }

            return $newProduct;
        });

        return redirect()->route('own-products.index')
            ->with('success', 'Producto duplicado exitosamente.');
    }
}