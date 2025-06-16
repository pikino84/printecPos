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
            $table->string('codigo')->unique(); // Código único para identificar el almacén
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->integer('ciudad')->nullable(); // ciudad del almacén
            $table->timestamps();

            $table->unique(['provider_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouses');
    }
};
