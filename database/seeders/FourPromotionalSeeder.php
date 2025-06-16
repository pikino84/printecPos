<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductProvider;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FourPromotionalSeeder extends Seeder
{
    public function run()
    {
       
        if (!Storage::exists('4promotional/products.json')) {
            Log::error('Archivo no encontrado: doblevela/products.json');
            return;
        }
         $json = Storage::get('4promotional/products.json');

        $products = json_decode($json, true);
          

        $provider = ProductProvider::firstOrCreate(
            ['slug' => '4promotional'],
            ['name' => '4Promotional']
        );

       foreach ($products as $product) {
            $productCategory = ProductCategory::firstOrCreate([
                'slug' => Str::slug($product['categoria']), 
            ], [
                'product_provider_id' => $provider->id,
                'name' => $product['categoria'],
                'subcategory' => $product['sub_categoria'] ?? null,
                'is_active' => true,
            ]);

            //Product Main Image
            $main_image = null;
            $imageName = null;
            $imagePath = null;
            $main_image = $product['imagenes'][0]['url_imagen'] ?? null;
            if ($main_image !== null) {
                // Codificar espacio sin romper la URL
                $main_image = str_replace(' ', '%20', $main_image);
                try {
                    $response = Http::withOptions(['verify' => false])
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->get($main_image);

                    if ($response->successful()) {
                        $imageName = Str::slug($product['id_articulo'] ?? uniqid()) . '.jpg';
                        $imagePath = "products/4promotional/{$imageName}";
                        Storage::disk('public')->put($imagePath, $response->body());
                    } else {
                        $this->command->warn("No se pudo descargar imagen (status {$response->status()}): $main_image");
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Error al descargar imagen: " . $e->getMessage());
                }

            }


            $productUpdateOrCreate = Product::updateOrCreate([
                'slug' => Str::slug($product['id_articulo']),
            ], [
                'model_code' => $product['id_articulo'],
                'name' => $product['nombre_articulo'],
                'description' => $product['descripcion'],
                'short_description' => null,
                'material' => null,
                'packing_type' => null,
                'unit_package' => $product['piezas_caja'] ?? null,
                'box_size' => $product['alto_caja'] . ' X ' . $product['ancho_caja'] . ' X ' . $product['largo_caja'] . ' X '   ?? null,
                'box_weight' => $product['peso_caja'] ?? null,
                'product_weight' => null,
                'product_size' => $product['medida_producto_alto'] . ' X ' . $product['medida_producto_ancho'] ?? null,
                'area_print' => $product['area_impresion'] ?? null,                
                'meta_description' => $product['descripcion'] ?? null,
                'meta_keywords' => $product['nombre_articulo'] ?? null,
                'featured' => false,
                'new' => false,
                'catalog_page' => null,
                'main_image' => $imagePath,
                'product_category_id' => $productCategory->id,
                'product_provider_id' => $provider->id,
            ]);

            $productUpdateOrCreate->save();


            //Product variant Image
            $main_image = null;
            $imageName = null;
            $imagePath = null;
            $sku = $product['id_articulo'].'-'.$product['color'];
            
            $main_image = $product['imagenes'][1]['url_imagen'] ?? null;
            if ($main_image !== null) {
                // Codificar espacio sin romper la URL
                $main_image = str_replace(' ', '%20', $main_image);
                try {
                    $response = Http::withOptions(['verify' => false])
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->get($main_image);

                    if ($response->successful()) {
                        $imageName = Str::slug($sku ?? uniqid()) . '.jpg';
                        $imagePath = "products/4promotional/{$imageName}";
                        Storage::disk('public')->put($imagePath, $response->body());
                    } else {
                        $this->command->warn("No se pudo descargar imagen (status {$response->status()}): $main_image");
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Error al descargar imagen: " . $e->getMessage());
                }

            }

            $productVariant = ProductVariant::updateOrCreate([
                'sku' => $sku
            ], [    
                'product_id' => $productUpdateOrCreate->id,
                'slug' => Str::slug($sku),
                'color_name' => $product['color'],                            
                'code_name' => $sku,
                'image' => $imagePath
            ]);

            $productVariant->save();


            $warehouses = \App\Models\ProductWarehouse::all();
            foreach ($warehouses as $warehouse) {
                if ($warehouse->codigo === '4promo-001') {                    
                    \App\Models\ProductStock::updateOrCreate([
                        'variant_id' => $productVariant->id,
                        'warehouse_id' => $warehouse->id,
                    ], [
                        'stock' => $product['inventario'] ?? 0,
                    ]);
                }
            }
            
            $image_product = $product['imagenes'][2]['url_imagen'] ?? null;
            //$image_product contiene https:\/\/4promotional.net:9090\/WsEstrategia\/imagesWeb\/imagenColor?id=LE 011&d=1958 pero tiene espacios enblanco
            if ($image_product !== null) {
                // Codificar espacio sin romper la URL
                $image_product = str_replace(' ', '%20', $image_product);
                try {
                    $response = Http::withOptions(['verify' => false])
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->get($image_product);

                    if ($response->successful()) {
                        $imageName = Str::slug($product['id_articulo'] . '-' . $sku) . '.jpg';
                        $imagePath = "products/4promotional/{$imageName}";
                        Storage::disk('public')->put($imagePath, $response->body());
                    } else {
                        $this->command->warn("No se pudo descargar imagen (status {$response->status()}): $image_product");
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Error al descargar imagen: " . $e->getMessage());
                }

            }
            
        }
    }
}
