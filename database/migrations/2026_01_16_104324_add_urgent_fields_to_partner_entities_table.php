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
        Schema::table('partner_entities', function (Blueprint $table) {
            $table->decimal('urgent_fee_percentage', 5, 2)->nullable()->after('payment_terms')
                ->comment('Porcentaje extra por trabajo urgente');
            $table->unsignedSmallInteger('urgent_days_limit')->nullable()->after('urgent_fee_percentage')
                ->comment('Días límite para considerar trabajo urgente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_entities', function (Blueprint $table) {
            $table->dropColumn(['urgent_fee_percentage', 'urgent_days_limit']);
        });
    }
};
