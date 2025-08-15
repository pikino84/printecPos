<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partner_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('razon_social');
            $table->string('rfc', 20)->nullable()->index();
            $table->string('telefono')->nullable();
            $table->string('correo_contacto')->nullable();
            $table->text('direccion')->nullable();
            $table->string('logo')->nullable();

            // Opcionales útiles
            $table->boolean('is_default')->default(false); // marcar la principal
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Evita duplicar la misma razón social dentro del mismo partner
            $table->unique(['partner_id','razon_social']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_entities');
    }
};
