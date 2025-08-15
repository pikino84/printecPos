<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\ProductWarehouse;
use App\Models\Partner;

class ProductWarehouseInnovationSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Partner Innovation
        $partner = Partner::where('slug', 'innovation')->firstOrFail();

        // 2) Cargar JSON
        $payload = json_decode(Storage::get('innovation/stock.json'), true);
        $data = $payload['data'] ?? [];

        // 3) Detectar llaves presentes en existencias
        $present = [];
        foreach ($data as $prod) {
            foreach (($prod['existencias'] ?? []) as $ex) {
                foreach ($ex as $k => $v) {
                    if (preg_match('/^(stock_(algarin|nuevo_cedis|fiscal|externo)|almacen_\d+|general_stock|apartados)$/', $k)) {
                        $present[$k] = true;
                    }
                }
            }
        }

        // 4) Mapa rawKey -> [codigo, nombre legible]
        $map = [
            'stock_algarin'      => ['algarin',      'Innovation Almacén Algarín'],
            'stock_nuevo_cedis'  => ['nuevo_cedis',  'Innovation Nuevo CEDIS'],
            'stock_fiscal'       => ['fiscal',       'Innovation Fiscal'],
            'stock_externo'      => ['externo',      'Innovation Externo'],
            'general_stock'      => ['stock',        'Innovation Stock General'],
            'apartados'          => ['apartados',    'Innovation Apartados'],
        ];

        // Agregar dinámicamente los numéricos encontrados: almacen_15..20
        foreach (array_keys($present) as $raw) {
            if (preg_match('/^almacen_(\d+)$/', $raw, $m)) {
                $num = (string) $m[1];
                $map[$raw] = [$num, "Innovation Almacén {$num}"];
            }
        }

        // 5) Ciudades sugeridas (slug) -> city_id
        $cityByCodigo = [
            'algarin' => 'cdmx',
            'nuevo_cedis' => 'gdl',
            'fiscal' => 'mty',
            'externo' => 'gdl',
            '15' => 'gdl', '16' => 'cun', '17' => 'cdmx', '18' => 'cdmx', '19' => 'gdl', '20' => 'gdl',
            'stock' => 'gdl', 'apartados' => 'gdl',
        ];
        $cityIds = DB::table('product_warehouses_cities')->pluck('id', 'slug')->toArray();

        // 6) Upsert idempotente
        $created = 0; $updated = 0;

        DB::transaction(function () use ($map, $partner, $cityByCodigo, $cityIds, &$created, &$updated) {
            foreach ($map as [$codigo, $name]) {
                $where = ['partner_id' => $partner->id, 'codigo' => (string) $codigo];
                $cityId = null;
                if (isset($cityByCodigo[$codigo])) {
                    $citySlug = $cityByCodigo[$codigo];
                    $cityId = $cityIds[$citySlug] ?? null;
                    if (!$cityId && method_exists($this->command, 'warn')) {
                        $this->command->warn("Ciudad [$citySlug] no existe para codigo [$codigo].");
                    }
                }

                $data = [
                    'name'       => $name,
                    'nickname'   => null,
                    'city_id'    => $cityId,
                    'is_active'  => 1,
                    'updated_at' => now(),
                ];

                $exists = ProductWarehouse::where($where)->first();
                if ($exists) {
                    $exists->update($data);
                    $updated++;
                } else {
                    ProductWarehouse::create($where + $data + ['created_at' => now()]);
                    $created++;
                }
            }
        });

        $this->command?->info("Innovation warehouses: creados $created, actualizados $updated.");

    }
}
