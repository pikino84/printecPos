<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('product_warehouses')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Un usuario solo puede tener una entrada por variante+warehouse
            $table->unique(['user_id', 'variant_id', 'warehouse_id'], 'cart_user_variant_warehouse_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_sessions');
    }
};