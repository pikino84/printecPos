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
        Schema::create('partner_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->unique()->constrained('partners')->cascadeOnDelete();
            $table->decimal('markup_percentage', 5, 2)->default(0); // % de ganancia del partner
            $table->foreignId('current_tier_id')->nullable()->constrained('pricing_tiers')->nullOnDelete();
            $table->decimal('last_month_purchases', 12, 2)->default(0); // Compras del mes pasado
            $table->decimal('current_month_purchases', 12, 2)->default(0); // Compras del mes actual
            $table->date('tier_assigned_at')->nullable(); // Fecha de asignación del tier
            $table->boolean('manual_tier_override')->default(false); // Si el tier fue asignado manualmente
            $table->timestamps();
            
            // Índices
            $table->index('current_tier_id');
            $table->index('last_month_purchases');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_pricing');
    }
};