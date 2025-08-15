<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_warehouses', function (Blueprint $table) {
            $table->id();
            // El almacén pertenece al proveedor (partner) que lo opera
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            // Código interno del almacén: único por partner/proveedor
            $table->string('codigo'); // ej. inno-algarin
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            // Ciudad (catálogo de ciudades)
            $table->foreignId('city_id')->nullable()->constrained('product_warehouses_cities')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unicidad por proveedor
            $table->unique(['partner_id','codigo']);

            // Índices útiles
            $table->index(['partner_id','city_id']);
            $table->index('nickname');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouses');
    }
};
