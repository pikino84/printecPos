<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DumpSeedersFromDB extends Command
{
    protected $signature = 'db:dump-seeders 
        {tables* : Lista de tablas a exportar (separadas por espacio)} 
        {--path=database/seeders/FromDb : Carpeta destino}
        {--class-suffix=TableSeeder : Sufijo para la clase}
        {--chunk=500 : Registros por bloque}
        {--indent=4 : Espacios de indentación}
        {--with-timestamps : Fuerza incluir created_at/updated_at aunque sean null}
        {--no-ids : Excluye la columna id}
    ';

    protected $description = 'Genera seeders a partir de datos existentes en las tablas';

    public function handle()
    {
        $tables = $this->argument('tables');
        $dest = base_path($this->option('path'));
        $chunkSize = (int)$this->option('chunk');
        $indent = str_repeat(' ', (int)$this->option('indent'));
        $includeTimestamps = (bool)$this->option('with-timestamps');
        $excludeId = (bool)$this->option('no-ids');

        if (!File::exists($dest)) {
            File::makeDirectory($dest, 0775, true);
        }

        foreach ($tables as $table) {
            $this->info("Procesando tabla: {$table}");

            // Columnas
            $columns = array_map(fn($col) => $col->Field, DB::select("SHOW COLUMNS FROM `{$table}`"));

            // Opcionalmente excluir id
            if ($excludeId && in_array('id', $columns, true)) {
                $columns = array_values(array_filter($columns, fn($c) => $c !== 'id'));
            }

            // created_at/updated_at: por defecto se omiten si están NULL en toda la tabla
            if (!$includeTimestamps) {
                foreach (['created_at','updated_at','deleted_at'] as $ts) {
                    if (in_array($ts, $columns, true)) {
                        $hasAnyNotNull = (bool) DB::table($table)->whereNotNull($ts)->limit(1)->count();
                        if (!$hasAnyNotNull) {
                            $columns = array_values(array_filter($columns, fn($c) => $c !== $ts));
                        }
                    }
                }
            }

            $total = DB::table($table)->count();
            if ($total === 0) {
                $this->warn("  - Sin registros; se creará un seeder vacío.");
            } else {
                $this->info("  - Registros: {$total}");
            }

            $className = Str::studly(Str::singular($table)) . $this->option('class-suffix');
            $filePath  = "{$dest}/{$className}.php";

            $body = [];
            $body[] = "<?php\n";
            $body[] = "namespace Database\\Seeders\\FromDb;";
            $body[] = "";
            $body[] = "use Illuminate\\Database\\Seeder;";
            $body[] = "use Illuminate\\Support\\Facades\\DB;";
            $body[] = "use Illuminate\\Support\\Facades\\Schema;";
            $body[] = "";
            $body[] = "class {$className} extends Seeder";
            $body[] = "{";
            $body[] = "    public function run(): void";
            $body[] = "    {";
            $body[] = "        Schema::disableForeignKeyConstraints();";
            $body[] = "        DB::table('{$table}')->truncate();";
            $body[] = "";

            if ($total > 0) {
                $offset = 0;
                while ($offset < $total) {
                    $rows = DB::table($table)
                        ->select($columns)
                        ->offset($offset)
                        ->limit($chunkSize)
                        ->get()
                        ->map(function ($row) {
                            return (array) $row;
                        })
                        ->toArray();

                    $phpArray = var_export($rows, true);

                    // Formateo bonito
                    $phpArray = preg_replace('/^array \\(/', '[', $phpArray);
                    $phpArray = preg_replace('/\\n\\s*\\),/m', "],", $phpArray);
                    $phpArray = preg_replace('/\\)$/', ']', $phpArray);

                    $lines = explode("\n", $phpArray);
                    $lines = array_map(fn($l) => $indent . $indent . $l, $lines);

                    $body[] = "        DB::table('{$table}')->insert(";
                    $body = array_merge($body, $lines);
                    $body[] = "        );";
                    $body[] = "";
                    $offset += $chunkSize;
                }
            }

            $body[] = "        Schema::enableForeignKeyConstraints();";
            $body[] = "    }";
            $body[] = "}";

            File::put($filePath, implode("\n", $body));
            $this->info("  ✔ Seeder generado: {$filePath}");
        }

        $this->newLine();
        $this->line('Añade estas clases a DatabaseSeeder o ejecútalas por separado con --class.');
        return 0;
    }
}
