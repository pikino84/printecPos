<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Productos: tipo de unidad. null = contable (default), 'metro_cuadrado' / 'metro_lineal'.
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'unit_type')) {
                $table->string('unit_type', 30)->nullable()->after('price');
            }
        });

        // quote_items.quantity: integer → decimal(10,2). Preserva valores existentes.
        Schema::table('quote_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
        });

        // cart_sessions.quantity: integer → decimal(10,2).
        Schema::table('cart_sessions', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_sessions', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->change();
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'unit_type')) {
                $table->dropColumn('unit_type');
            }
        });
    }
};
