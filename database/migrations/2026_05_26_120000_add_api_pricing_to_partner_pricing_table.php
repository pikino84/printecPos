<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pricing independiente para el widget/API público del partner.
     * Ambos campos son nullable: si están vacíos, el widget hereda el
     * valor del catálogo (markup_percentage / current_tier_id).
     */
    public function up(): void
    {
        Schema::table('partner_pricing', function (Blueprint $table) {
            // % de ganancia del partner SOLO para el widget/API. NULL = heredar del catálogo.
            $table->decimal('api_markup_percentage', 5, 2)->nullable()->after('markup_percentage');
            // Nivel de precio SOLO para el widget/API. NULL = heredar del catálogo.
            $table->foreignId('api_tier_id')->nullable()->after('current_tier_id')
                ->constrained('pricing_tiers')->nullOnDelete();

            $table->index('api_tier_id');
        });
    }

    public function down(): void
    {
        Schema::table('partner_pricing', function (Blueprint $table) {
            $table->dropForeign(['api_tier_id']);
            $table->dropIndex(['api_tier_id']);
            $table->dropColumn(['api_markup_percentage', 'api_tier_id']);
        });
    }
};
