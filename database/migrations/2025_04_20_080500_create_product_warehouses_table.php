<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('product_providers')->onDelete('cascade');
            $table->unsignedInteger('codigo');
            $table->string('name')->nullable(); // Añadido
            $table->string('nickname')->nullable(); // Añadido
            $table->timestamps();

            $table->unique(['provider_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouses');
    }
};
