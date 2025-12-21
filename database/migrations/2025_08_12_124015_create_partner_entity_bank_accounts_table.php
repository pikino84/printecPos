<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partner_entity_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_entity_id')->constrained('partner_entities')->cascadeOnDelete();

            $table->string('bank_name');                 // BBVA, Santander, etc.
            $table->string('account_holder')->nullable(); // Titular (si difiere)
            $table->string('account_number')->nullable(); // usa string para ceros a la izquierda
            $table->string('card_number', 20)->nullable(); // Número de tarjeta
            $table->string('clabe', 18)->nullable();      // MX CLABE 18 dígitos
            $table->string('swift')->nullable();
            $table->string('iban')->nullable();
            $table->string('currency', 3)->default('MXN'); // ISO 4217
            $table->string('alias')->nullable();           // “Nómina”, “Operaciones”, etc.
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Evita duplicados evidentes
            $table->unique(['partner_entity_id','account_number'], 'peba_eid_acc_uq');
            $table->unique(['partner_entity_id','clabe'], 'peba_eid_clabe_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_entity_bank_accounts');
    }
};
