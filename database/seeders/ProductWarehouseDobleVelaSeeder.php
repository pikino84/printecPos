<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\ProductWarehouse;
use App\Models\Partner;

class ProductWarehouseDobleVelaSeeder extends Seeder
{
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

        // 1) Recolectar TODOS los codigos de almacén desde "Disponible" y "Comprometido"
        $codes = collect($json)->flatMap(function(array $item) {
            return collect(array_keys($item))->map(function($key){
                if (preg_match('/^(Disponible|Comprometido)\s+Almacen\s+(\d+)$/u', $key, $m)) {
                    return (string)$m[2]; // normalizamos a string
                }
                return null;
            })->filter();
        })->unique()->values();

        // (Opcional) nickname/city si quieres prellenar algo aquí
        $nickByCode = [
            '7'  => 'CDMX Centro',
            '8'  => 'CDMX Sur',
            '9'  => 'CDMX GAM',
            '10' => 'CDMX Norte',
            '20' => 'CDMX Este',
            '24' => 'CDMX Oeste',
        ];

        $created = 0; $updated = 0;

        DB::transaction(function () use ($codes, $partner, $nickByCode, &$created, &$updated) {
            foreach ($codes as $code) {
                [$model, $wasCreated] = ProductWarehouse::updateOrCreate(
                    ['partner_id' => $partner->id, 'codigo' => $code],
                    [
                        'name'      => "Doble Vela Almacén {$code}",
                        'nickname'  => $nickByCode[$code] ?? null, // o déjalo null si prefieres mapear en otro seeder
                        'is_active' => 1,
                    ]
                )->wasRecentlyCreated
                    ? [$code, true]
                    : [$code, false];

                $wasCreated ? $created++ : $updated++;
            }
        });

        $this->command?->info("Doble Vela - Almacenes: creados {$created}, actualizados {$updated}.");
    }
}
