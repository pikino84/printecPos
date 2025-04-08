<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('color')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('stock_total')->default(0);
            $table->integer('reserved')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('arrival_qty_1')->nullable();
            $table->string('arrival_date_1')->nullable();
            $table->string('arrival_qty_2')->nullable();
            $table->string('arrival_date_2')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_variants');
    }
};