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
                $existing = ProductWarehouse::where('partner_id', $partner->id)
                    ->where('codigo', $code)
                    ->first();

                if ($existing) {
                    // Solo actualizar name e is_active, NO sobrescribir nickname
                    $existing->update([
                        'name'      => "Doble Vela Almacén {$code}",
                        'is_active' => 1,
                    ]);
                    $updated++;
                } else {
                    // Crear nuevo con nickname por defecto
                    ProductWarehouse::create([
                        'partner_id' => $partner->id,
                        'codigo'     => $code,
                        'name'       => "Doble Vela Almacén {$code}",
                        'nickname'   => $nickByCode[$code] ?? null,
                        'is_active'  => 1,
                    ]);
                    $created++;
                }
            }
        });

        $this->command?->info("Doble Vela - Almacenes: creados {$created}, actualizados {$updated}.");
    }
}
