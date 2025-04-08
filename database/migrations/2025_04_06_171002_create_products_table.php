<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->string('material')->nullable();
            $table->string('packing_type')->nullable();
            $table->string('impression_type')->nullable();
            $table->string('unit_package')->nullable();
            $table->string('box_size')->nullable();
            $table->string('box_weight')->nullable();
            $table->string('product_weight')->nullable();
            $table->string('product_size')->nullable();
            $table->string('model_code')->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};