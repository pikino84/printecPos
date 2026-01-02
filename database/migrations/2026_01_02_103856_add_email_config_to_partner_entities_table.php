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
        Schema::table('partner_entities', function (Blueprint $table) {
            // Configuración SMTP
            $table->string('smtp_host')->nullable()->after('payment_terms');
            $table->integer('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->text('smtp_password')->nullable()->after('smtp_username'); // Encriptado
            $table->enum('smtp_encryption', ['tls', 'ssl', 'none'])->default('tls')->after('smtp_password');
            $table->string('mail_from_address')->nullable()->after('smtp_encryption');
            $table->string('mail_from_name')->nullable()->after('mail_from_address');

            // Correos para copia (CC) separados por coma
            $table->text('mail_cc_addresses')->nullable()->after('mail_from_name');

            // Indica si la configuración de correo está activa/verificada
            $table->boolean('mail_configured')->default(false)->after('mail_cc_addresses');
        });
    }

    public function down(): void
    {
        Schema::table('partner_entities', function (Blueprint $table) {
            $table->dropColumn([
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'mail_from_address',
                'mail_from_name',
                'mail_cc_addresses',
                'mail_configured',
            ]);
        });
    }
};
