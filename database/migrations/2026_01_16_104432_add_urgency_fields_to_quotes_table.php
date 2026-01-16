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
        Schema::table('quotes', function (Blueprint $table) {
            $table->boolean('is_urgent')->default(false)->after('total')
                ->comment('Indica si es trabajo urgente');
            $table->decimal('urgency_fee', 12, 2)->default(0)->after('is_urgent')
                ->comment('Cargo por urgencia aplicado');
            $table->decimal('urgency_percentage', 5, 2)->nullable()->after('urgency_fee')
                ->comment('Porcentaje de urgencia aplicado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['is_urgent', 'urgency_fee', 'urgency_percentage']);
        });
    }
};
