<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Identificación y SEO
            $table->string('name');
            $table->string('slug');
            $table->string('model_code')->nullable();
            
            // Datos comerciales
            $table->decimal('price', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('short_description', 500)->nullable();
             // Atributos físicos
            $table->string('material')->nullable();
            $table->string('packing_type')->nullable();
            $table->string('unit_package')->nullable();
            $table->string('box_size')->nullable();
            $table->string('box_weight')->nullable();
            $table->string('product_weight')->nullable();
            $table->string('product_size')->nullable();
            $table->string('area_print')->nullable();
             // Meta
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('new')->default(false);
            $table->integer('catalog_page')->nullable();
            // Imágenes
            $table->string('main_image')->nullable();
            // Relaciones
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            // proveedor real: Doble Vela, 4Promotional, etc. (si es propio de Printec, será 1)
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            // dueño/publicador: Printec (1) o el partner que lo capturó
            $table->foreignId('owner_id')->constrained('partners')->cascadeOnDelete();
            // auditoría
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // ✅ Unicidad por partner + slug (evita choques entre proveedores)
            $table->unique(['partner_id', 'slug'], 'products_partner_slug_unique');


            $table->index(['owner_id','is_active']);
            $table->index(['product_category_id','owner_id']);
            $table->index(['partner_id', 'name']);
            $table->index('name');
            $table->index('model_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
