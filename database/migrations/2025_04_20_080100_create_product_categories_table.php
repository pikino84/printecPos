<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            
            // ✅ Campos obligatorios (NO NULL)
            $table->string('name')->nullable(false);
            $table->string('slug')->nullable(false);
            
            // Campos opcionales
            $table->string('subcategory')->nullable();
            
            // Relación con partners (obligatoria)
            $table->foreignId('partner_id')
                ->nullable(false)
                ->constrained('partners')
                ->cascadeOnDelete();
            
            // Estado activo (por defecto true)
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();

            // ✅ Unicidad: partner + slug (evita duplicados por proveedor)
            $table->unique(['partner_id', 'slug'], 'pcat_partner_slug_unique');

            // ✅ Índices para optimizar búsquedas
            $table->index(['partner_id', 'name']);
            $table->index(['partner_id', 'is_active']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};