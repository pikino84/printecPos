<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\ProductWarehouse;
use App\Models\Partner;

class ProductWarehouseDobleVelaSeeder extends Seeder
{
    /**
     * Almacenes oficiales según documentación Doble Vela.
     * Solo estos deben sumarse para calcular el inventario correcto.
     */
    public const OFFICIAL_WAREHOUSE_CODES = ['7', '9', '15', '20', '24'];

    public function run(): void
    {
        $path = storage_path('app/doblevela/products.json');
        if (!File::exists($path)) {
            $this->command?->warn("No existe {$path}. Se omite.");
            return;
        }

        $json = json_decode(File::get($path), true);
        if (!is_array($json)) {
            $this->command?->warn("JSON inválido en {$path}. Se omite.");
            return;
        }

        $partner = Partner::where('slug', 'doble-vela')->first();
        if (!$partner) {
            $this->command?->error("Partner 'doble-vela' no encontrado.");
            return;
        }

        // Recolectar TODOS los codigos de almacén desde "Disponible"
        $codes = collect($json)->flatMap(function (array $item) {
            return collect(array_keys($item))->map(function ($key) {
                if (preg_match('/^Disponible\s+Almacen\s+(\d+)$/u', $key, $m)) {
                    return (string) $m[1];
                }
                return null;
            })->filter();
        })->unique()->values();

        // Nicknames conocidos según documentación oficial
        $nickByCode = [
            '7'  => 'CDMX Almacén 7',
            '9'  => 'CDMX Almacén 9',
            '15' => 'CDMX Almacén 15',
            '20' => 'CDMX Almacén 20',
            '24' => 'CDMX Almacén 24',
        ];

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($codes, $partner, $nickByCode, &$created, &$updated) {
            foreach ($codes as $code) {
                $isOfficial = in_array($code, self::OFFICIAL_WAREHOUSE_CODES, true);

                $existing = ProductWarehouse::where('partner_id', $partner->id)
                    ->where('codigo', $code)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'name'      => "Doble Vela Almacén {$code}",
                        'is_active' => $isOfficial ? 1 : 0,
                    ]);
                    $updated++;
                } else {
                    ProductWarehouse::create([
                        'partner_id' => $partner->id,
                        'codigo'     => $code,
                        'name'       => "Doble Vela Almacén {$code}",
                        'nickname'   => $nickByCode[$code] ?? null,
                        'is_active'  => $isOfficial ? 1 : 0,
                    ]);
                    $created++;
                }
            }
        });

        $officialCount = $codes->filter(fn ($c) => in_array($c, self::OFFICIAL_WAREHOUSE_CODES, true))->count();
        $this->command?->info("Doble Vela - Almacenes: creados {$created}, actualizados {$updated}. Oficiales activos: {$officialCount}");
    }
}
