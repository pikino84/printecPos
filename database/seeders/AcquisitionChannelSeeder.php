<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcquisitionChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            // De tu imagen
            ['name' => '(Select All)', 'order' => 0],
            ['name' => 'CAMPAÑA 20WORDS', 'order' => 10],
            ['name' => 'Doble Vela', 'order' => 20],
            ['name' => 'Email', 'order' => 30],
            ['name' => 'Facebook', 'order' => 40],
            ['name' => 'Google Maps', 'order' => 50],
            ['name' => 'Local Walk inn', 'order' => 60],
            ['name' => 'Mercado Libre', 'order' => 70],
            ['name' => 'Pagina Web', 'order' => 80],
            ['name' => 'Referido', 'order' => 90],
            ['name' => 'Teléfono', 'order' => 100],
            ['name' => 'VENTAS', 'order' => 110],
        ];

        foreach ($channels as $channel) {
            DB::table('acquisition_channels')->insert([
                'name' => $channel['name'],
                'slug' => Str::slug($channel['name']),
                'description' => null,
                'is_active' => true,
                'order' => $channel['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}