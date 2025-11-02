<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Str;

class OwnProductsExampleSeeder extends Seeder
{
    public function run(): void
    {
        $printecPartner = Partner::where('slug', 'printec')->first();
        $adminUser = User::where('partner_id', $printecPartner->id)->first();

        // Crear categoría para productos propios de Printec
        $category = ProductCategory::firstOrCreate(
            ['slug' => 'printec-propios', 'partner_id' => $printecPartner->id],
            ['name' => 'Productos Propios Printec', 'is_active' => true]
        );

        // Producto propio de Printec (público para asociados)
        $product = Product::create([
            'name' => 'Playera Printec Premium',
            'slug' => 'printec-playera-premium-' . Str::random(4),
            'model_code' => 'PRINTEC-001',
            'price' => 299.00,
            'description' => 'Playera de algodón 100% con logo Printec. Perfecta para eventos corporativos.',
            'short_description' => 'Playera premium con logo Printec',
            'material' => 'Algodón 100%',
            'product_size' => 'Tallas: S, M, L, XL',
            'area_print' => 'Pecho: 20x20cm',
            'product_category_id' => $category->id,
            'partner_id' => $printecPartner->id,
            'owner_id' => $printecPartner->id,
            'created_by' => $adminUser->id,
            'is_active' => true,
            'featured' => true,
            // CAMPOS DE PRODUCTO PROPIO
            'is_own_product' => true,  // Es producto propio
            'is_public' => true,       // Visible para asociados
        ]);

        // Crear variantes
        $colores = ['Blanco', 'Negro', 'Azul'];
        foreach ($colores as $index => $color) {
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => 'PRINTEC-001-' . strtoupper(substr($color, 0, 3)),
                'slug' => Str::slug("playera-{$color}") . '-' . Str::random(4),
                'color_name' => $color,
                'price' => 100.00, // Usa precio del producto principal
            ]);
        }

        $this->command->info('Productos propios de ejemplo creados para Printec.');
    }
}