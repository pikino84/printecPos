<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductProvider;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\ProductStock;
use App\Models\ProductImpressionTechnique;
use App\Models\ProductPriceScale;

class InnovationSeeder extends Seeder
{
    public function run(): void
    {
        $provider = ProductProvider::firstOrCreate(
            ['slug' => 'innovation'],
            ['name' => 'Innovation']
        );

        $productsJson = json_decode(Storage::get('innovation/products.json'), true);
        $stockJson = json_decode(Storage::get('innovation/stock.json'), true);
        $salePriceJson = json_decode(Storage::get('innovation/sale_prices.json'), true);

        $existingWarehouses = ProductWarehouse::pluck('id')->toArray();

        $products = collect($productsJson['data']);
        $stockData = collect($stockJson['data']);
        $salePriceData = collect($salePriceJson['data']);

        foreach ($products as $productData) {
            $id_product_inno = $productData['idProducto'] ?? null;
            $mainSlug = Str::slug($productData['codigo']);
            $productCategory = null;

            $cat = $productData['categorias']['categorias'][0] ?? null;
            if ($cat) {
                $productCategory = ProductCategory::firstOrCreate([
                    'slug' => Str::slug($cat['codigo']),
                ], [
                    'name' => $cat['nombre'],
                    'subcategory' => $cat['subcategoria'] ?? null,
                    'product_provider_id' => $provider->id,
                ]);
            }

            $product = Product::updateOrCreate(
                ['slug' => $mainSlug],
                [
                    'name' => $productData['nombre'],
                    'description' => $productData['descripcion'],
                    'material' => $productData['material'],
                    'unit_package' => $productData['cantidad_por_paquete'],
                    'box_size' => $productData['medidas_paquete'],
                    'box_weight' => $productData['peso_paquete'],
                    'product_weight' => $productData['peso_producto'],
                    'product_size' => $productData['medidas_producto'],
                    'model_code' => $productData['codigo'],
                    'product_provider_id' => $provider->id,
                    'product_category_id' => $productCategory->id ?? null,
                    'area_print' => $productData['area_impresion'] ?? null,
                    'meta_description' => $productData['meta_description'] ?? null,
                    'meta_keywords' => $productData['meta_keywords'] ?? null,
                ]
            );

            // Imagen principal
            if (!empty($productData['imagen_principal'])) {
                $mainUrl = $productData['imagen_principal'];
                $imageName = basename($mainUrl);
                $localPath = "products/innovation/{$imageName}";
                $fullPath = storage_path("app/public/{$localPath}");

                if (!file_exists($fullPath)) {
                    try {
                        $imageContent = file_get_contents($mainUrl);
                        file_put_contents($fullPath, $imageContent);
                    } catch (\Exception $e) {
                        Log::error("Error al guardar imagen principal: {$mainUrl}", ['error' => $e->getMessage()]);
                    }
                }

                $product->main_image = $localPath;
                $product->save();
            }

            // Imágenes adicionales
            foreach ($productData['imagenes_adicionales'] ?? [] as $imgUrl) {
                $imgName = basename($imgUrl);
                $imgPath = "products/innovation/{$imgName}";
                $fullStorage = storage_path("app/public/{$imgPath}");

                if (!file_exists($fullStorage)) {
                    try {
                        $content = file_get_contents($imgUrl);
                        file_put_contents($fullStorage, $content);
                    } catch (\Exception $e) {
                        Log::error("Error al guardar imagen adicional: {$imgUrl}", ['error' => $e->getMessage()]);
                    }
                }

            }
            // Stock
            $warehouses = \App\Models\ProductWarehouse::all();
            foreach ($stockData as $stockItem) {
                foreach ($warehouses as $warehouse) {
                    // Separar el código del almacén en prefijo y sufijo: inno-algarin, inno-15, etc.
                    $warehouseParts = explode('-', $warehouse->codigo);
                    if (count($warehouseParts) < 2) {
                        continue; // Evitar errores si el código no tiene guion
                    }
                    $warehousePrefix = $warehouseParts[0]; // 'inno'
                    $warehouseSuffix = $warehouseParts[1]; // 'algarin', '15', etc.
                    if ($warehousePrefix !== 'inno') {
                        continue;
                    }
                    foreach ($stockItem['existencias'] as $keyStock => $valeuStock) {                            
                        // Saltar valores vacíos o no numéricos
                        if (!is_array($valeuStock)) {
                            continue;
                        }                            
                        foreach ($valeuStock as $key => $value) {
                            // El key puede ser: stock_algarin, almacen_15, general_stock, etc.
                            $nameParts = explode('_', $key);                               
                            $stockSuffix = end($nameParts); // ejemplo: 'algarin', '15', etc.
                            if ($stockSuffix == $warehouseSuffix) {                                    
                                Log::info("Actualizando stock: {$valeuStock[$key] }");
                                \App\Models\ProductStock::updateOrCreate([
                                    'variant_id' => $variant->id,
                                    'warehouse_id' => $warehouse->id,
                                ], [
                                    'stock' => $valeuStock[$key] ?? 0,
                                ]);
                                break 2; // Ya se encontró el almacén correspondiente
                            }
                        }
                    }
                }
            }

            // Técnicas de impresión
            foreach ($productData['tecnicas_impresion'] ?? [] as $tech) {
                ProductImpressionTechnique::firstOrCreate([
                    'product_id' => $product->id,
                    'code' => $tech['codigo'],
                ], [
                    'name' => $tech['nombre'],
                ]);
            }

            // Variantes
            foreach ($productData['colores'] ?? [] as $color) {
                $imageUrl = $color['image'];
                $imgName = basename(parse_url($imageUrl, PHP_URL_PATH));
                $imgLocalPath = "products/innovation/{$imgName}";
                $imgFullPath = storage_path("app/public/{$imgLocalPath}");

                if (!file_exists($imgFullPath)) {
                    try {
                        $imgContent = file_get_contents($imageUrl);
                        file_put_contents($imgFullPath, $imgContent);
                    } catch (\Exception $e) {
                        Log::error("Error imagen color: {$imageUrl}", ['error' => $e->getMessage()]);
                    }
                }

                $variant = ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sku' => $color['clave'],
                        'slug' => Str::slug($color['clave']),
                    ],
                    [
                        'image' => $imgLocalPath,
                        'code_name' => $color['clave'],
                        'color_name' => $color['color'],
                    ]
                );                
            }

            // Escalas de precio
            $prices = $salePriceData->firstWhere('codigo', $productData['codigo'])['precios_venta'] ?? [];
            foreach ($prices as $price) {
                ProductPriceScale::updateOrCreate([
                    'product_id' => $product->id,
                    'scale' => $price['escala'],
                ], [
                    'price' => $price['precio'],
                ]);
            }
        }
    }
}