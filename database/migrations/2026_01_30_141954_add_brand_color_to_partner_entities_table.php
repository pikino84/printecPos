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
            $table->string('brand_color', 7)->nullable()->after('logo_path')->comment('Color de marca en formato hexadecimal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_entities', function (Blueprint $table) {
            $table->dropColumn('brand_color');
        });
    }
};
