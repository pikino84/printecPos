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
        Schema::create('partner_tier_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('tier_id')->constrained('pricing_tiers')->cascadeOnDelete();
            $table->decimal('purchases_amount', 12, 2)->default(0); // Monto de compras que determinó el tier
            $table->date('period_start'); // Inicio del período (ej: 2025-01-01)
            $table->date('period_end'); // Fin del período (ej: 2025-01-31)
            $table->boolean('is_manual')->default(false); // Si fue asignado manualmente
            $table->text('notes')->nullable(); // Notas adicionales
            $table->timestamps();
            
            // Índices
            $table->index(['partner_id', 'period_start']);
            $table->index('tier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_tier_history');
    }
};