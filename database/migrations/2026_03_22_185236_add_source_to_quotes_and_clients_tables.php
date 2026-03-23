<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar campo source a quotes
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('source', 20)->default('system')->after('status');
        });

        // Agregar campo source a clients
        Schema::table('clients', function (Blueprint $table) {
            $table->string('source', 20)->default('system')->after('is_active');
        });

        // Agregar 'pending' al enum de status en quotes
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired', 'invoiced', 'paid', 'pending') DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('draft', 'sent', 'accepted', 'rejected', 'expired', 'invoiced', 'paid') DEFAULT 'draft'");
    }
};
