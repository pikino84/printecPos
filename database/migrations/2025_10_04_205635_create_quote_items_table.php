<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('product_warehouses')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // Precio al momento de cotizar
            $table->decimal('subtotal', 12, 2); // quantity * unit_price
            $table->timestamps();

            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};