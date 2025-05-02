<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductProvider;

class ProductProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['slug' => 'innovation', 'name' => 'Innovation'],
            ['slug' => 'doble-vela', 'name' => 'Doble Vela'],
        ];

        foreach ($providers as $provider) {
            ProductProvider::firstOrCreate(['slug' => $provider['slug']], [
                'name' => $provider['name'],
            ]);
        }
    }
}
