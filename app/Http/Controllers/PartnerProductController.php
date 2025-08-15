<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ProductWarehouse;





class PartnerProductController extends Controller
{
    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        $warehouses = ProductWarehouse::orderBy('nickname')->get();
        return view('partners.products.create', compact('categories','warehouses'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name'        => 'required|string|max:255',
            'model_code'  => 'required|string|max:100',
            'product_category_id' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'main_image'  => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
            'variants'    => 'required|array|min:1',
            'variants.*.sku'   => 'required|string|max:100',
            'variants.*.color_name' => 'nullable|string|max:100',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        DB::transaction(function() use ($r){
            $product = Product::create([
                'name' => $r->name,
                'slug' => Str::slug($r->name.'-'.$r->model_code.'-'.Str::random(4)),
                'model_code' => $r->model_code,
                'description' => $r->description,
                'product_category_id' => $r->product_category_id,
                'partner_id' => auth()->user()->partner_id,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            // Imagen principal del producto
            if ($r->hasFile('main_image')) {
                $path = $r->file('main_image')->store('products/'.$product->id, 'public');
                $product->update(['main_image' => $path]);
            }

            // Variantes + imagen + stocks por almacén
            foreach ($r->variants as $i => $row) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku'        => $row['sku'],
                    'slug'       => Str::slug(($row['sku'] ?? $r->name).'-'.Str::random(4)),
                    'code_name'  => $row['code_name'] ?? null,
                    'color_name' => $row['color_name'] ?? null,
                    'price'      => $row['price'],
                ]);

                // archivo de la variante: variants[i][image]
                if ($file = $r->file("variants.$i.image")) {
                    $vPath = $file->store("products/{$product->id}/variants", 'public');
                    $variant->update(['image' => $vPath]);
                }

                // stocks: variants[i][stocks][warehouse_id] => qty
                if (!empty($row['stocks']) && is_array($row['stocks'])) {
                    foreach ($row['stocks'] as $warehouseId => $qty) {
                        if ($qty === '' || $qty === null) continue;
                        ProductStock::updateOrCreate(
                            ['variant_id' => $variant->id, 'warehouse_id' => $warehouseId],
                            ['stock' => (int)$qty]
                        );
                    }
                }
            }
        });

        return redirect()->route('partner-products.index')->with('success','Producto creado.');
    }

    public function edit(Product $partner_product)
    {
        $this->authorize('update', $partner_product);
        $partner_product->load(['variants.stocks']);
        $categories = ProductCategory::orderBy('name')->get();
        $warehouses = ProductWarehouse::orderBy('nickname')->get();
        return view('partners.products.edit', compact('partner_product','categories','warehouses'));
    }

    public function update(Request $r, Product $partner_product)
    {
        $this->authorize('update', $partner_product);

        $r->validate([
            'name'        => 'required|string|max:255',
            'model_code'  => 'required|string|max:100',
            'product_category_id' => 'required|exists:product_categories,id',
            'description' => 'nullable|string',
            'main_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'variants'    => 'required|array|min:1',
            'variants.*.id'    => 'nullable|exists:product_variants,id',
            'variants.*.sku'   => 'required|string|max:100',
            'variants.*.color_name' => 'nullable|string|max:100',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        DB::transaction(function() use ($r, $partner_product){
            $partner_product->update([
                'name' => $r->name,
                'model_code' => $r->model_code,
                'description' => $r->description,
                'product_category_id' => $r->product_category_id,
            ]);

            if ($r->hasFile('main_image')) {
                $path = $r->file('main_image')->store('products/'.$partner_product->id, 'public');
                $partner_product->update(['main_image' => $path]);
            }

            // Actualiza/crea variantes (no elimina las que se quiten en la UI)
            foreach ($r->variants as $i => $row) {
                $variant = isset($row['id'])
                    ? ProductVariant::where('product_id', $partner_product->id)->findOrFail($row['id'])
                    : new ProductVariant(['product_id' => $partner_product->id]);

                $variant->fill([
                    'sku'        => $row['sku'],
                    'slug'       => $variant->exists ? $variant->slug : Str::slug(($row['sku'] ?? $r->name).'-'.Str::random(4)),
                    'code_name'  => $row['code_name'] ?? null,
                    'color_name' => $row['color_name'] ?? null,
                    'price'      => $row['price'],
                ])->save();

                if ($file = $r->file("variants.$i.image")) {
                    $vPath = $file->store("products/{$partner_product->id}/variants", 'public');
                    $variant->update(['image' => $vPath]);
                }

                if (!empty($row['stocks']) && is_array($row['stocks'])) {
                    foreach ($row['stocks'] as $warehouseId => $qty) {
                        if ($qty === '' || $qty === null) continue;
                        ProductStock::updateOrCreate(
                            ['variant_id' => $variant->id, 'warehouse_id' => $warehouseId],
                            ['stock' => (int)$qty]
                        );
                    }
                }
            }
        });

        return back()->with('success','Producto actualizado.');
    }
}
