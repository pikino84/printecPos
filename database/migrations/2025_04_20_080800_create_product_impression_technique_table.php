<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_impression_technique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('code')->nullable(); // Código interno de la técnica
            $table->string('name'); // Nombre de la técnica
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_impression_technique');
    }
};
