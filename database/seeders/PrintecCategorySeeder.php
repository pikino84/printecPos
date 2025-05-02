<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrintecCategory;

class PrintecCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Tecnología', 'slug' => 'tecnologia'],
            ['name' => 'Oficina', 'slug' => 'oficina'],
            ['name' => 'Salud y Belleza', 'slug' => 'salud-belleza'],
            ['name' => 'Hogar', 'slug' => 'hogar'],
            ['name' => 'Viaje y Recreación', 'slug' => 'viaje-recreacion'],
            ['name' => 'Textiles', 'slug' => 'textiles'],
            ['name' => 'Sublimación', 'slug' => 'sublimacion'],
            ['name' => 'Cajas de Regalo', 'slug' => 'cajas-de-regalo'],
            ['name' => 'Bebidas', 'slug' => 'bebidas'],
            ['name' => 'Escritura', 'slug' => 'escritura'],
            ['name' => 'Ecología', 'slug' => 'ecologia'],
        ];

        foreach ($categories as $category) {
            PrintecCategory::create($category);
        }
    }
}
