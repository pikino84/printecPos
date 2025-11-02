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
            $table->string('slug')->unique();
            $table->string('code_name')->nullable();
            $table->string('color_name')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['product_id','sku']);
            $table->index('sku');
            $table->index('color_name');
            
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_variants');
    }
};
