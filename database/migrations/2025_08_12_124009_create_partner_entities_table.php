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
            $table->string('logo_path')->nullable();

            // Certificados CSD para timbrado CFDI
            $table->string('csd_cer_path')->nullable();
            $table->string('csd_key_path')->nullable();
            $table->text('csd_password')->nullable(); // Encriptado con Laravel encrypt()
            $table->date('csd_valid_from')->nullable();
            $table->date('csd_valid_until')->nullable();

            // Datos fiscales adicionales requeridos por el SAT
            $table->string('fiscal_regime', 10)->nullable(); // c_RegimenFiscal (ej: 601, 612, 626)
            $table->string('zip_code', 5)->nullable(); // Código postal del domicilio fiscal

            // Serie y folio para facturas
            $table->string('invoice_series', 10)->default('A');
            $table->unsignedInteger('invoice_next_folio')->default(1);

            // Opcionales útiles
            $table->boolean('is_default')->default(false); // marcar la principal
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index('partner_id');

            // Evita duplicar la misma razón social dentro del mismo partner
            $table->unique(['partner_id','razon_social']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_entities');
    }
};
