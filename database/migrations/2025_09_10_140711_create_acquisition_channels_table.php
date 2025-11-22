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
        Schema::create('acquisition_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nombre del canal (ej: "CAMPAÑA 20 WORDS")
            $table->string('slug')->unique(); // Slug para URLs/código
            $table->text('description')->nullable(); // Descripción del canal
            $table->boolean('is_active')->default(true); // Si el canal está activo
            $table->integer('order')->default(0); // Orden de visualización
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquisition_channels');
    }
};