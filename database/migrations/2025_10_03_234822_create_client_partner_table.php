<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_partner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->timestamp('first_contact_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Un cliente solo puede tener una relación con cada partner
            $table->unique(['client_id', 'partner_id']);
            
            // Índices
            $table->index('client_id');
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_partner');
    }
};