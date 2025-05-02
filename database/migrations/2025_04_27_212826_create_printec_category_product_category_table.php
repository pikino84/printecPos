<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printec_category_product_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printec_category_id')->constrained('printec_categories')->onDelete('cascade');
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printec_category_product_category');
    }
};
