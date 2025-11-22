<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Junior, Distribuidor, Básico A, etc.
            $table->string('slug')->unique();
            $table->decimal('min_monthly_purchases', 12, 2)->default(0); // Mínimo de compras mensuales
            $table->decimal('max_monthly_purchases', 12, 2)->nullable(); // Máximo (null = sin límite)
            $table->decimal('discount_percentage', 5, 2)->default(0); // Porcentaje de descuento
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // Orden de visualización
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('is_active');
            $table->index(['min_monthly_purchases', 'max_monthly_purchases']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};