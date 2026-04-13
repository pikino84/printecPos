<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->after('unit_price')
                  ->comment('Precio base del proveedor al momento de cotizar');
        });

        // Backfill: llenar cost_price desde el precio base del producto
        DB::statement("
            UPDATE quote_items qi
            JOIN product_variants pv ON qi.variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            SET qi.cost_price = p.price
            WHERE qi.cost_price IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
