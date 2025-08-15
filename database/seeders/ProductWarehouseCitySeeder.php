<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Partner;
use App\Models\ProductWarehouse;

class ProductWarehouseCitySeeder extends Seeder
{
    public function run(): void
    {
        $cityIds = DB::table('product_warehouses_cities')->pluck('id','slug')->toArray();
        $partnerIds = Partner::whereIn('slug', ['doble-vela','innovation','4promotional'])
            ->pluck('id','slug')->toArray();

        $updates = [
            // Doble Vela (códigos numéricos normalizados)
            ['partner_slug'=>'doble-vela','codigo'=>'7',  'city_slug'=>'cdmx', 'nickname'=>'CDMX Centro'],
            ['partner_slug'=>'doble-vela','codigo'=>'8',  'city_slug'=>'cdmx', 'nickname'=>'CDMX Sur'],
            ['partner_slug'=>'doble-vela','codigo'=>'9',  'city_slug'=>'cdmx', 'nickname'=>'CDMX GAM'],
            ['partner_slug'=>'doble-vela','codigo'=>'10', 'city_slug'=>'cdmx', 'nickname'=>'CDMX Norte'],
            ['partner_slug'=>'doble-vela','codigo'=>'20', 'city_slug'=>'cdmx', 'nickname'=>'CDMX Este'],
            ['partner_slug'=>'doble-vela','codigo'=>'24', 'city_slug'=>'cdmx', 'nickname'=>'CDMX Oeste'],

            // Innovation (nombres + numéricos normalizados)
            ['partner_slug'=>'innovation','codigo'=>'algarin',      'city_slug'=>'cdmx', 'nickname'=>'CDMX Centro'],
            ['partner_slug'=>'innovation','codigo'=>'nuevo_cedis',  'city_slug'=>'gdl',  'nickname'=>'GDL Norte'],
            ['partner_slug'=>'innovation','codigo'=>'fiscal',       'city_slug'=>'mty',  'nickname'=>'MTY Externo'],
            ['partner_slug'=>'innovation','codigo'=>'externo',      'city_slug'=>'gdl',  'nickname'=>'GDL Externo'],
            ['partner_slug'=>'innovation','codigo'=>'15',           'city_slug'=>'gdl',  'nickname'=>'GDL Norte'],
            ['partner_slug'=>'innovation','codigo'=>'16',           'city_slug'=>'cun',  'nickname'=>'Cancún Portillo'],
            ['partner_slug'=>'innovation','codigo'=>'17',           'city_slug'=>'cdmx', 'nickname'=>'CDMX Sur'],
            ['partner_slug'=>'innovation','codigo'=>'18',           'city_slug'=>'cdmx', 'nickname'=>'CDMX Eje SUR'],
            ['partner_slug'=>'innovation','codigo'=>'19',           'city_slug'=>'gdl',  'nickname'=>'GDL Belisario'],
            ['partner_slug'=>'innovation','codigo'=>'20',           'city_slug'=>'gdl',  'nickname'=>'GDL Chapu'],
            ['partner_slug'=>'innovation','codigo'=>'stock',        'city_slug'=>'gdl',  'nickname'=>'GDL Stock'],
            ['partner_slug'=>'innovation','codigo'=>'apartados',    'city_slug'=>'gdl',  'nickname'=>'GDL Apartados'],

            // 4Promotional
            ['partner_slug'=>'4promotional','codigo'=>'001',        'city_slug'=>'cdmx', 'nickname'=>'CDMX Centro'],
        ];

        $ok=0; $skip=0;

        foreach ($updates as $u) {
            $pid = $partnerIds[$u['partner_slug']] ?? null;
            if (!$pid) { $this->command?->warn("Partner no encontrado: {$u['partner_slug']}"); $skip++; continue; }

            $wh = ProductWarehouse::where('partner_id',$pid)->where('codigo',$u['codigo'])->first();
            if (!$wh) { $this->command?->warn("Warehouse no encontrado: {$u['partner_slug']} / {$u['codigo']}"); $skip++; continue; }

            $cityId = $u['city_slug'] ? ($cityIds[$u['city_slug']] ?? null) : null;
            if ($u['city_slug'] && !$cityId) { $this->command?->warn("Ciudad no encontrada: {$u['city_slug']}"); }

            $wh->update(['city_id'=>$cityId,'nickname'=>$u['nickname']]);
            $ok++;
        }

        $this->command?->info("WarehouseCity patch: actualizados {$ok}, omitidos {$skip}.");
    }
}
