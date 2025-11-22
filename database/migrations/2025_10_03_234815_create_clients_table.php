<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('rfc', 13)->nullable();
            $table->text('direccion')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('is_active')->default(true);
        
            $table->foreignId('acquisition_channel_id')
                ->nullable()
                ->constrained('acquisition_channels')
                ->nullOnDelete();
            
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('email');
            $table->index('rfc');
            $table->index('razon_social');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};