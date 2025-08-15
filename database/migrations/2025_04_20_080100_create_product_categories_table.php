<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subcategory')->nullable();
            $table->string('slug');
            $table->foreignId('partner_id')
                ->constrained('partners')
                ->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // ✅ Unicidad por partner + slug (evita choques entre proveedores)
            $table->unique(['partner_id','slug'], 'pcat_partner_slug_unique');

            // (Opcionales) índices útiles
            $table->index(['partner_id','name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
