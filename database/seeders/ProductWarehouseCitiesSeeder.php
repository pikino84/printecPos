<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductWarehousesCities; 

class ProductWarehouseCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'cdmx' => 'CDMX',
            'gdl'  => 'Guadalajara',
            'mty'  => 'Monterrey',
            'cun'  => 'Cancún',
        ];

        $created = 0; $updated = 0;

        foreach ($cities as $slug => $name) {
            $model = ProductWarehousesCities::updateOrCreate(
                ['slug' => $slug],     // clave idempotente
                ['name' => $name]      // datos actualizables
            );

            $model->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->command?->info("Cities: creadas {$created}, actualizadas {$updated}.");
    }
}
