<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_entity_id')->constrained()->cascadeOnDelete();

            // Identificadores del CFDI
            $table->string('invoice_number')->unique(); // Número interno (INV-2025-0001)
            $table->char('uuid', 36)->nullable()->unique(); // UUID del timbre fiscal
            $table->string('series', 10); // Serie (A, B, etc.)
            $table->unsignedInteger('folio'); // Folio consecutivo

            // Tipo de CFDI
            $table->enum('cfdi_type', ['I', 'E', 'P', 'N', 'T'])->default('I'); // Ingreso, Egreso, Pago, Nómina, Traslado
            $table->string('payment_form', 3)->default('99'); // c_FormaPago (01=Efectivo, 03=Transferencia, 99=Por definir)
            $table->string('payment_method', 3)->default('PUE'); // PUE=Pago en una exhibición, PPD=Pago en parcialidades
            $table->string('cfdi_use', 5)->default('G03'); // c_UsoCFDI (G01=Adquisición mercancías, G03=Gastos en general)

            // Datos del receptor (cliente) - copiados de la cotización al momento de facturar
            $table->string('receptor_rfc', 13);
            $table->string('receptor_name');
            $table->string('receptor_fiscal_regime', 10)->nullable();
            $table->string('receptor_zip_code', 5)->nullable();
            $table->string('receptor_email')->nullable();

            // Montos
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(16.00); // Porcentaje de IVA
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('MXN');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);

            // Estado
            $table->enum('status', ['draft', 'stamped', 'cancelled', 'pending_cancel'])->default('draft');

            // Contenido del CFDI
            $table->longText('xml_content')->nullable(); // XML timbrado
            $table->string('pdf_path')->nullable(); // Ruta al PDF generado

            // Fechas importantes
            $table->timestamp('stamped_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 4)->nullable(); // Motivo de cancelación SAT (01, 02, 03, 04)
            $table->char('replacement_uuid', 36)->nullable(); // UUID de factura que sustituye (para cancelación 01)

            // Para pagos parciales (50% y 50%)
            $table->unsignedTinyInteger('payment_number')->default(1); // 1 = primera factura, 2 = segunda factura
            $table->unsignedTinyInteger('total_payments')->default(1); // Total de facturas para esta cotización

            $table->timestamps();

            // Índices
            $table->index(['quote_id', 'payment_number']);
            $table->index('status');
            $table->index('created_at');
            $table->index(['partner_entity_id', 'series', 'folio']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Claves del catálogo SAT
            $table->string('product_key', 10)->default('01010101'); // c_ClaveProdServ genérica
            $table->string('unit_key', 5)->default('E48'); // c_ClaveUnidad (E48=Unidad de servicio, H87=Pieza)
            $table->string('unit_name', 50)->default('Servicio'); // Nombre de la unidad

            // Descripción del producto/servicio
            $table->string('sku')->nullable(); // SKU del producto
            $table->text('description');

            // Cantidades y precios
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 12, 4);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);

            // Impuestos por línea
            $table->decimal('tax_rate', 5, 2)->default(16.00);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            // Referencia al item original de la cotización
            $table->foreignId('quote_item_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
