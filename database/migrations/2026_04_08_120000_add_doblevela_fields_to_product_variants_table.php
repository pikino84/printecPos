<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('price_list')->nullable()->after('price');
            $table->string('status', 20)->nullable()->after('price_list');
            $table->integer('apartado')->default(0)->after('status');
            $table->integer('por_llegar_1')->default(0)->after('apartado');
            $table->date('fecha_llegada_1')->nullable()->after('por_llegar_1');
            $table->integer('por_llegar_2')->default(0)->after('fecha_llegada_1');
            $table->date('fecha_llegada_2')->nullable()->after('por_llegar_2');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn([
                'price_list',
                'status',
                'apartado',
                'por_llegar_1',
                'fecha_llegada_1',
                'por_llegar_2',
                'fecha_llegada_2',
            ]);
        });
    }
};
