<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ProductWarehouse;
use App\Models\Partner;

class ProductWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Mapa slug partner -> id
        $partnerIdsBySlug = Partner::query()
            ->whereIn('slug', ['doble-vela','innovation','4promotional'])
            ->pluck('id','slug')
            ->toArray();

        // 2) Ciudades (slug -> id)
        $cityIds = DB::table('product_warehouses_cities')->pluck('id','slug')->toArray();

        // 3) Datos
        $warehouses = [
            // Doble Vela
            ['codigo'=>'7',  'name'=>'Doble Vela Almacén', 'nickname'=>'C E D I S',     'partner_slug'=>'doble-vela', 'city_slug'=>'cdmx'],
            // Innovation
            /*['codigo'=>'15', 'name'=>'Almacén General',     'nickname'=>'ALGARÍN',       'partner_slug'=>'innovation',  'city_slug'=>'cdmx'],
            ['codigo'=>'16', 'name'=>'Almacén 16',          'nickname'=>'M T Y',         'partner_slug'=>'innovation',  'city_slug'=>'mty'],
            ['codigo'=>'17', 'name'=>'Almacén 17',          'nickname'=>'CDMX',          'partner_slug'=>'innovation',  'city_slug'=>'cdmx'],
            ['codigo'=>'18', 'name'=>'Almacén 18',          'nickname'=>'CANCUN',        'partner_slug'=>'innovation',  'city_slug'=>'cun'],
            ['codigo'=>'19', 'name'=>'Almacén 19',          'nickname'=>'EXTERNO 24 HR', 'partner_slug'=>'innovation',  'city_slug'=>'gdl'],
            ['codigo'=>'20', 'name'=>'Almacén 20',          'nickname'=>null,            'partner_slug'=>'innovation',  'city_slug'=>null],*/
            // 4Promotional
            ['codigo'=>'001','name'=>'4Promotional 001',    'nickname'=>'CDMX Centro','partner_slug'=>'4promotional','city_slug'=>'cdmx'],
        ];

        $created = 0; $updated = 0;

        DB::transaction(function () use ($warehouses, $partnerIdsBySlug, $cityIds, &$created, &$updated) {
            foreach ($warehouses as $w) {
                $partnerId = $partnerIdsBySlug[$w['partner_slug']] ?? null;
                if (!$partnerId) {
                    $this->command?->warn("Partner slug [{$w['partner_slug']}] no encontrado. Saltando.");
                    continue;
                }

                $codigo = (string) $w['codigo'];
                $cityId = $w['city_slug'] ? ($cityIds[$w['city_slug']] ?? null) : null;
                if ($w['city_slug'] && !$cityId) {
                    $this->command?->warn("Ciudad [{$w['city_slug']}] no existe. Asignando null.");
                }

                // Unicidad por partner + codigo (recomendado en migración)
                $where = ['partner_id' => $partnerId, 'codigo' => $codigo];

                $data = [
                    'name'       => $w['name'] ?? ($w['nickname'] ?: $codigo),
                    'nickname'   => $w['nickname'],
                    'city_id'    => $cityId,
                    'is_active'  => 1,
                    'updated_at' => now(),
                ];

                $exists = ProductWarehouse::where($where)->first();

                if ($exists) {
                    $exists->update($data);
                    $updated++;
                } else {
                    ProductWarehouse::create(array_merge($where, $data));
                    $created++;
                }
            }
        });

        $this->command->info("Almacenes: creados $created, actualizados $updated.");
    }
}
