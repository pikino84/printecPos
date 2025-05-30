<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductProvider;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Log;

class DobleVelaSeeder extends Seeder
{
    public function run()
    {
        $provider = ProductProvider::firstOrCreate([
            'slug' => 'doble-vela',
        ], [
            'name' => 'Doble Vela'
        ]);

        if (!Storage::exists('doblevela/products.json')) {
            Log::error('Archivo no encontrado: doblevela/products.json');
            return;
        }
        $json = Storage::get('doblevela/products.json');
        $products = json_decode($json, true);

        $existingWarehouses = \App\Models\ProductWarehouse::pluck('id')->toArray();


        $grouped = collect($products)->groupBy('MODELO');

        foreach ($grouped as $model => $items) {
            $first = $items->first();

            $slug = Str::slug($first['Familia']);

            $productCategory = ProductCategory::firstOrCreate([
                'slug' => $slug, 
            ], [
                'name' => $first['Familia'],
                'subcategory' => $first['SubFamilia'],
                'product_provider_id' => $provider->id,
            ]);
            

            $product = Product::updateOrCreate([
                'slug' => Str::slug($model),
            ], [
                'name' => $first['Nombre Corto'] ?? $first['NOMBRE'],
                'description' => $first['Descripcion'],
                'material' => $first['Material'] ?? null,
                'unit_package' => $first['Unidad Empaque'] ?? null,
                'box_size' => $first['Medida Caja Master'] ?? null,
                'box_weight' => $first['Peso caja'] ?? null,
                'product_weight' => $first['Peso Producto'] ?? null,
                'product_size' => $first['Medida Producto'] ?? null,
                'model_code' => $model,
                'product_provider_id' => $provider->id,
                'meta_description' => $first['Nombre Corto'] ?? null,
                'meta_keywords' => $first['Tipo Impresion'] ?? null,
                'product_category_id' => $productCategory->id,
            ]);

            $product->save();
            


            // Imagen principal del producto
            $mainImageName = "{$model}_lrg.jpg";
            $mainImageUrl = "https://www.doblevela.com/images/large/" . rawurlencode($mainImageName);
            $mainLocalPath = "products/doblevela/{$mainImageName}";
            $mainStoragePath = storage_path("app/public/{$mainLocalPath}");

            $this->downloadImage($mainImageUrl, $mainStoragePath);

            $product->main_image = $mainLocalPath;
            $product->save();

            foreach ($items as $data) {
                $parts = explode('-', $data['COLOR']);
                $color_name = mb_strtolower(trim(end($parts)), 'UTF-8');

                $color_slug = Str::slug($data['COLOR']);
                $color_image = explode("-", $data['COLOR']);
                $color_image = trim( end($color_image) );
                $color_image = str_replace(' ', '', mb_strtolower($color_image, 'UTF-8'));
                $modelo = str_replace(' ', '', $data['MODELO']);
                $variantImageName = "{$modelo}_{$color_image}_lrg.jpg";
                $variantImageUrl = "https://www.doblevela.com/images/large/" . rawurlencode($variantImageName);
                $variantLocalPath = "products/doblevela/{$variantImageName}";
                $variantStoragePath = storage_path("app/public/{$variantLocalPath}");

                $this->downloadImage($variantImageUrl, $variantStoragePath);
                

                // Generar nombre base de imagen
                $variantImageName = "{$model}_{$color_name}_lrg.jpg";
                $variantImageUrl = "https://www.doblevela.com/images/large/" . rawurlencode($variantImageName);
                
                
                $productVariant = ProductVariant::firstOrNew([
                    'sku' => $data['CLAVE'],
                ]);
                $productVariant->fill([
                    'product_id' => $product->id,
                    'slug' => Str::slug($data['CLAVE']),
                    'code_name' => $data['CLAVE'],
                    'color_name' => $color_name,
                    'image' => $variantLocalPath,
                ]);

                $productVariant->save();


                $warehouses = [
                    7 => intval($first['Disponible Almacen 7'] ?? 0),
                    8 => intval($first['Disponible Almacen 8'] ?? 0),
                    9 => intval($first['Disponible Almacen 9'] ?? 0),
                    10 => intval($first['Disponible Almacen 10'] ?? 0),
                    20 => intval($first['Disponible Almacen 20'] ?? 0),
                    24 => intval($first['Disponible Almacen 24'] ?? 0),
                ];
                
                foreach ($warehouses as $warehouseId => $quantity) {
                    if ($quantity > 0) {
                        if (!in_array($warehouseId, $existingWarehouses)) {
                            \App\Models\ProductWarehouse::create([
                                'id' => $warehouseId,
                                'provider_id' => $provider->id,
                                'codigo' => $warehouseId,
                                'name' => 'Almacén ' . $warehouseId,
                                'nickname' => null,
                            ]);
                            $existingWarehouses[] = $warehouseId; // 👈🏻 Actualizas el array en memoria
                        }
                
                        \App\Models\ProductStock::create([
                            'variant_id' => $productVariant->id,
                            'warehouse_id' => $warehouseId,
                            'stock' => $quantity,
                        ]);
                    }
                }
                
                

                
            }
        }
    }

    private function downloadImage($url, $path)
    {
        try {
            if (!file_exists($path)) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                $imageData = curl_exec($ch);
                curl_close($ch);

                if ($imageData !== false) {
                    file_put_contents($path, $imageData);
                } else {
                    Log::warning("No se pudo descargar la imagen: $url");
                }
            }
        } catch (\Exception $e) {
            Log::error("Error descargando imagen: {$url} - " . $e->getMessage());
        }
    }
}
