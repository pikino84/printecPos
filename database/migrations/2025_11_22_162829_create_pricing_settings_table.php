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
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // printec_markup, tax_rate, etc.
            $table->string('value'); // Valor del setting
            $table->string('type')->default('decimal'); // decimal, integer, boolean, string
            $table->string('label'); // Etiqueta para mostrar en UI
            $table->text('description')->nullable();
            $table->string('group')->default('general'); // general, pricing, tax, etc.
            $table->boolean('is_editable')->default(true); // Si puede editarse desde UI
            $table->timestamps();
            
            // Índices
            $table->index('key');
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_settings');
    }
};