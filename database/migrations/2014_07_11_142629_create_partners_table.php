<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('direccion')->nullable();
            $table->string('type');
            $table->text('commercial_terms')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_active')->default(true);
            
            // ✅ Solo la columna, SIN foreign key todavía
            $table->unsignedBigInteger('default_entity_id')->nullable();
            
            $table->timestamps();
        });
    }
};
