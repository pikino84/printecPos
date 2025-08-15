<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Partner;
use App\Models\ProductWarehouse;

class ProductWarehouseFourPromotionalSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Buscar el partner 4Promotional
        $partner = Partner::where('slug', '4promotional')->first();

        if (!$partner) {
            $this->command?->warn('Partner [4promotional] no encontrado. Omite seeder.');
            return;
        }

        // 2) (Opcional) Obtener ciudad: usaremos CDMX
        $cityId = DB::table('product_warehouses_cities')
            ->where('slug', 'cdmx')
            ->value('id');

        // 3) Definir su único almacén
        $where = [
            'partner_id' => $partner->id,
            'codigo'     => '001', // clave estable que ya usas
        ];

        $data = [
            'name'       => '4Promotional Almacén 001',
            'nickname'   => 'CDMX Centro',
            'city_id'    => $cityId,    // puede quedar null si la ciudad no existe
            'is_active'  => 1,
            'updated_at' => now(),
        ];

        // 4) Upsert idempotente
        $existing = ProductWarehouse::where($where)->first();
        if ($existing) {
            $existing->update($data);
            $this->command?->info('Almacén 4Promotional actualizado.');
        } else {
            ProductWarehouse::create($where + $data + ['created_at' => now()]);
            $this->command?->info('Almacén 4Promotional creado.');
        }
    }
}
