<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\PartnerEntity;
use App\Models\Quote;
use App\Services\CFDI\CFDIService;
use Illuminate\Console\Command;

class TestCFDIStamp extends Command
{
    protected $signature = 'cfdi:test-stamp
                            {--quote= : ID de la cotizacion a facturar}
                            {--dry-run : Solo mostrar el XML sin timbrar}';

    protected $description = 'Prueba el flujo de timbrado CFDI';

    public function handle(CFDIService $cfdiService)
    {
        $this->info('=== Test de Timbrado CFDI ===');
        $this->newLine();

        // Mostrar proveedor actual
        $this->info('Proveedor PAC: ' . $cfdiService->getProviderName());
        $this->info('Modo: ' . (config('cfdi.test_mode') ? 'PRUEBAS' : 'PRODUCCION'));
        $this->newLine();

        // Obtener entidad fiscal
        $entity = PartnerEntity::where('is_active', true)->first();
        if (!$entity) {
            $this->error('No hay entidades fiscales activas');
            return 1;
        }

        $this->info('Entidad Emisora:');
        $this->line("  RFC: {$entity->rfc}");
        $this->line("  Razon Social: {$entity->razon_social}");
        $this->line("  Regimen: {$entity->fiscal_regime}");
        $this->line("  CP: {$entity->zip_code}");
        $this->line("  Puede facturar: " . ($entity->canIssueInvoices() ? 'SI' : 'NO'));
        $this->newLine();

        if (!$entity->canIssueInvoices()) {
            $this->error('La entidad no tiene la configuracion fiscal completa');
            return 1;
        }

        // Obtener o crear factura de prueba
        $quoteId = $this->option('quote');

        if ($quoteId) {
            $quote = Quote::with('items')->find($quoteId);
            if (!$quote) {
                $this->error("Cotizacion #{$quoteId} no encontrada");
                return 1;
            }
        } else {
            // Buscar una cotizacion con items
            $quote = Quote::has('items')->first();
            if (!$quote) {
                $this->error('No hay cotizaciones con items disponibles');
                return 1;
            }
        }

        $this->info('Cotizacion seleccionada:');
        $this->line("  ID: {$quote->id}");
        $this->line("  Cliente: {$quote->client_name}");
        $this->line("  Items: " . $quote->items->count());
        $this->line("  Total: $" . number_format($quote->total, 2));
        $this->newLine();

        // Crear factura desde cotizacion
        $this->info('Creando factura desde cotizacion...');

        try {
            $invoice = Invoice::createFromQuote($quote, $entity);

            $this->info('Factura creada:');
            $this->line("  ID: {$invoice->id}");
            $this->line("  Numero: {$invoice->invoice_number}");
            $this->line("  Serie-Folio: {$invoice->series}-{$invoice->folio}");
            $this->line("  Subtotal: $" . number_format($invoice->subtotal, 2));
            $this->line("  IVA: $" . number_format($invoice->tax, 2));
            $this->line("  Total: $" . number_format($invoice->total, 2));
            $this->line("  Status: {$invoice->status}");
            $this->newLine();

            if ($this->option('dry-run')) {
                $this->warn('Modo dry-run: No se timbrara la factura');
                $this->info('La factura se creo correctamente en modo borrador');
                return 0;
            }

            // Intentar timbrar
            if (!$this->confirm('Deseas timbrar esta factura?', true)) {
                $this->info('Operacion cancelada');
                return 0;
            }

            $this->info('Enviando a timbrar...');
            $result = $cfdiService->stamp($invoice);

            if ($result->isSuccess()) {
                $invoice->refresh();

                $this->newLine();
                $this->info('*** TIMBRADO EXITOSO ***');
                $this->line("  UUID: {$invoice->uuid}");
                $this->line("  Status: {$invoice->status}");
                $this->line("  Timbrado: {$invoice->stamped_at}");

                // Mostrar metadata
                $metadata = $result->getMetadata();
                if (!empty($metadata)) {
                    $this->newLine();
                    $this->info('Metadata:');
                    foreach ($metadata as $key => $value) {
                        if (!is_array($value) && strlen($value) < 100) {
                            $this->line("  {$key}: {$value}");
                        }
                    }
                }

                return 0;
            } else {
                $this->newLine();
                $this->error('*** ERROR EN TIMBRADO ***');
                $this->line("  Error: " . $result->getError());
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
