<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('model_code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('material')->nullable();
            $table->string('packing_type')->nullable();
            $table->string('unit_package')->nullable();
            $table->string('box_size')->nullable();
            $table->string('box_weight')->nullable();
            $table->string('product_weight')->nullable();
            $table->string('product_size')->nullable();
            $table->string('area_print')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('new')->default(false);
            $table->integer('catalog_page')->nullable();
            $table->string('main_image')->nullable();
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->foreignId('product_provider_id')->constrained('product_providers')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
