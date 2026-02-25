<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['quote_number']);
            $table->dropIndex(['quote_number']);

            $table->unique(['partner_id', 'quote_number']);
            $table->index('quote_number');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['partner_id', 'quote_number']);
            $table->dropIndex(['quote_number']);

            $table->unique('quote_number');
            $table->index('quote_number');
        });
    }
};
