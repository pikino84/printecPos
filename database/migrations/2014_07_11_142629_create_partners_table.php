<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del partner
            $table->string('slug')->unique();
            $table->string('contact_name')->nullable(); // Nombre persona de contacto
            $table->string('contact_phone', 30)->nullable(); // Celular de contacto
            $table->string('contact_email')->nullable(); // Correo de contacto
            $table->text('direccion')->nullable(); // Dirección
            $table->enum('type', ['Proveedor', 'Asociado', 'Mixto'])->default('Mixto');
            $table->text('commercial_terms')->nullable(); // Condiciones comerciales
            $table->text('comments')->nullable(); // Comentarios
            $table->boolean('is_active')->default(true); // Activo o no
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
