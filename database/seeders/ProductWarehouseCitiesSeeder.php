<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductWarehousesCities;


class ProductWarehouseCitiesSeeder extends Seeder
{
    public function run()
    {
        $warehouses = [
            'cdmx' => 'CDMX',
            'gdl' => 'Guadalajara',
            'mty' => 'Monterrey',
            'cun' => 'Cancún',
        ];

        foreach ($warehouses as $key => $name) {
            ProductWarehousesCities::firstOrCreate(                
                [
                    'name' => $name,
                    'slug' => $key
                ]
            );
        }
    }
}