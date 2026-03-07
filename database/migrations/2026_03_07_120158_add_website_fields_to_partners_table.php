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
        Schema::table('partners', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('api_show_prices');
            $table->string('hero_desktop')->nullable()->after('logo');
            $table->string('hero_mobile')->nullable()->after('hero_desktop');
            $table->longText('contact_info')->nullable()->after('hero_mobile');
            $table->string('site_primary_color', 7)->nullable()->default('#007bff')->after('contact_info');
            $table->string('site_secondary_color', 7)->nullable()->default('#6c757d')->after('site_primary_color');
            $table->string('site_accent_color', 7)->nullable()->default('#28a745')->after('site_secondary_color');
            $table->string('site_header_footer_bg', 7)->nullable()->default('#ffffff')->after('site_accent_color');
            $table->string('site_catalog_bg', 7)->nullable()->default('#f8f9fa')->after('site_header_footer_bg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'logo',
                'hero_desktop',
                'hero_mobile',
                'contact_info',
                'site_primary_color',
                'site_secondary_color',
                'site_accent_color',
                'site_header_footer_bg',
                'site_catalog_bg',
            ]);
        });
    }
};
