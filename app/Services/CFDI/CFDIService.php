<?php

namespace App\Services\CFDI;

use App\Models\Invoice;
use App\Models\PartnerEntity;
use Illuminate\Support\Facades\Log;

class CFDIService
{
    protected PACInterface $pac;

    public function __construct(?PACInterface $pac = null)
    {
        // Por defecto usar el mock hasta que se configure un PAC real
        $this->pac = $pac ?? new MockPACProvider();
    }

    /**
     * Obtener el nombre del proveedor PAC actual
     */
    public function getProviderName(): string
    {
        return $this->pac->getProviderName();
    }

    /**
     * Timbrar una factura
     */
    public function stamp(Invoice $invoice): StampResult
    {
        try {
            // Validar que la factura puede ser timbrada
            if (!$invoice->isDraft()) {
                return StampResult::error('La factura ya fue timbrada o cancelada');
            }

            // Obtener la entidad emisora
            $entity = $invoice->partnerEntity;
            if (!$entity->canIssueInvoices()) {
                return StampResult::error('La entidad emisora no tiene la configuración fiscal completa');
            }

            // Generar el XML del CFDI
            $xml = $this->generateCFDIXml($invoice);

            // Enviar al PAC para timbrado
            $result = $this->pac->stamp($xml, $entity);

            if ($result->isSuccess()) {
                // Actualizar la factura con los datos del timbre
                $invoice->update([
                    'uuid' => $result->getUuid(),
                    'xml_content' => $result->getXml(),
                    'status' => 'stamped',
                    'stamped_at' => now(),
                ]);

                Log::info("Factura timbrada exitosamente", [
                    'invoice_id' => $invoice->id,
                    'uuid' => $result->getUuid(),
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error al timbrar factura", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return StampResult::error('Error al timbrar: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar una factura
     */
    public function cancel(Invoice $invoice, string $reason, ?string $replacementUuid = null): CancelResult
    {
        try {
            if (!$invoice->canBeCancelled()) {
                return CancelResult::error('La factura no puede ser cancelada');
            }

            $entity = $invoice->partnerEntity;

            $result = $this->pac->cancel(
                $invoice->uuid,
                $entity,
                $reason,
                $replacementUuid
            );

            if ($result->isSuccess()) {
                $invoice->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                    'replacement_uuid' => $replacementUuid,
                ]);

                Log::info("Factura cancelada exitosamente", [
                    'invoice_id' => $invoice->id,
                    'uuid' => $invoice->uuid,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error al cancelar factura", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return CancelResult::error('Error al cancelar: ' . $e->getMessage());
        }
    }

    /**
     * Generar el XML del CFDI (versión 4.0)
     */
    protected function generateCFDIXml(Invoice $invoice): string
    {
        $entity = $invoice->partnerEntity;
        $items = $invoice->items;

        // Fecha en formato ISO 8601
        $fecha = now()->format('Y-m-d\TH:i:s');

        // Construir el XML del CFDI 4.0
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd"
                Version="4.0"/>');

        // Atributos del comprobante
        $xml->addAttribute('Serie', $invoice->series);
        $xml->addAttribute('Folio', (string) $invoice->folio);
        $xml->addAttribute('Fecha', $fecha);
        $xml->addAttribute('FormaPago', $invoice->payment_form);
        $xml->addAttribute('SubTotal', number_format($invoice->subtotal, 2, '.', ''));
        $xml->addAttribute('Moneda', $invoice->currency);
        $xml->addAttribute('Total', number_format($invoice->total, 2, '.', ''));
        $xml->addAttribute('TipoDeComprobante', $invoice->cfdi_type);
        $xml->addAttribute('MetodoPago', $invoice->payment_method);
        $xml->addAttribute('LugarExpedicion', $entity->zip_code);
        $xml->addAttribute('Exportacion', '01'); // No aplica

        // Emisor
        $emisor = $xml->addChild('cfdi:Emisor');
        $emisor->addAttribute('Rfc', $entity->rfc);
        $emisor->addAttribute('Nombre', $entity->razon_social);
        $emisor->addAttribute('RegimenFiscal', $entity->fiscal_regime);

        // Receptor
        $receptor = $xml->addChild('cfdi:Receptor');
        $receptor->addAttribute('Rfc', $invoice->receptor_rfc);
        $receptor->addAttribute('Nombre', $invoice->receptor_name);
        $receptor->addAttribute('DomicilioFiscalReceptor', $invoice->receptor_zip_code ?? '00000');
        $receptor->addAttribute('RegimenFiscalReceptor', $invoice->receptor_fiscal_regime ?? '616');
        $receptor->addAttribute('UsoCFDI', $invoice->cfdi_use);

        // Conceptos
        $conceptos = $xml->addChild('cfdi:Conceptos');
        foreach ($items as $item) {
            $concepto = $conceptos->addChild('cfdi:Concepto');
            $concepto->addAttribute('ClaveProdServ', $item->product_key);
            $concepto->addAttribute('NoIdentificacion', $item->sku ?? '');
            $concepto->addAttribute('Cantidad', number_format($item->quantity, 4, '.', ''));
            $concepto->addAttribute('ClaveUnidad', $item->unit_key);
            $concepto->addAttribute('Unidad', $item->unit_name);
            $concepto->addAttribute('Descripcion', $item->description);
            $concepto->addAttribute('ValorUnitario', number_format($item->unit_price, 4, '.', ''));
            $concepto->addAttribute('Importe', number_format($item->subtotal, 2, '.', ''));
            $concepto->addAttribute('ObjetoImp', '02'); // Sí objeto de impuesto

            // Impuestos del concepto
            $impuestos = $concepto->addChild('cfdi:Impuestos');
            $traslados = $impuestos->addChild('cfdi:Traslados');
            $traslado = $traslados->addChild('cfdi:Traslado');
            $traslado->addAttribute('Base', number_format($item->subtotal, 2, '.', ''));
            $traslado->addAttribute('Impuesto', '002'); // IVA
            $traslado->addAttribute('TipoFactor', 'Tasa');
            $traslado->addAttribute('TasaOCuota', '0.160000');
            $traslado->addAttribute('Importe', number_format($item->tax_amount, 2, '.', ''));
        }

        // Impuestos totales
        $impuestosTotal = $xml->addChild('cfdi:Impuestos');
        $impuestosTotal->addAttribute('TotalImpuestosTrasladados', number_format($invoice->tax, 2, '.', ''));
        $trasladosTotal = $impuestosTotal->addChild('cfdi:Traslados');
        $trasladoTotal = $trasladosTotal->addChild('cfdi:Traslado');
        $trasladoTotal->addAttribute('Base', number_format($invoice->subtotal, 2, '.', ''));
        $trasladoTotal->addAttribute('Impuesto', '002');
        $trasladoTotal->addAttribute('TipoFactor', 'Tasa');
        $trasladoTotal->addAttribute('TasaOCuota', '0.160000');
        $trasladoTotal->addAttribute('Importe', number_format($invoice->tax, 2, '.', ''));

        return $xml->asXML();
    }

    /**
     * Cambiar el proveedor PAC
     */
    public function setPACProvider(PACInterface $pac): void
    {
        $this->pac = $pac;
    }
}
