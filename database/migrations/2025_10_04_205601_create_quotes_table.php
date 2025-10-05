<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->string('quote_number')->unique();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0)->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->string('short_description', 255)->nullable(); // NUEVO CAMPO
            $table->date('valid_until')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_to_email')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('quote_number');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};