<?php

namespace App\Services\CFDI;

use App\Models\PartnerEntity;
use Illuminate\Support\Str;

/**
 * Proveedor PAC de pruebas (Mock)
 *
 * Este proveedor simula las respuestas de un PAC real para propósitos de desarrollo.
 * IMPORTANTE: No usar en producción. Reemplazar por un proveedor real como:
 * - FinkokProvider
 * - FacturapiProvider
 * - SWProvider
 */
class MockPACProvider implements PACInterface
{
    /**
     * Simular timbrado de CFDI
     */
    public function stamp(string $xml, PartnerEntity $entity): StampResult
    {
        // Simular un pequeño delay como si fuera una llamada API real
        usleep(100000); // 100ms

        // Generar UUID simulado
        $uuid = Str::uuid()->toString();

        // Simular el XML timbrado (agregar el complemento TimbreFiscalDigital)
        $stampedXml = $this->addMockTimbre($xml, $uuid);

        return StampResult::success($uuid, $stampedXml, [
            'provider' => 'MockPAC',
            'environment' => 'development',
            'warning' => 'Este es un timbre de prueba. No válido fiscalmente.',
            'stamped_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Simular cancelación de CFDI
     */
    public function cancel(
        string $uuid,
        PartnerEntity $entity,
        string $reason,
        ?string $replacementUuid = null
    ): CancelResult {
        // Simular delay
        usleep(100000);

        // Validar motivo de cancelación
        $validReasons = ['01', '02', '03', '04'];
        if (!in_array($reason, $validReasons)) {
            return CancelResult::error('Motivo de cancelación inválido');
        }

        // Si el motivo es 01 (sustitución), debe tener UUID de reemplazo
        if ($reason === '01' && empty($replacementUuid)) {
            return CancelResult::error('Se requiere UUID de factura de reemplazo para el motivo 01');
        }

        return CancelResult::success('cancelled', [
            'provider' => 'MockPAC',
            'environment' => 'development',
            'cancelled_at' => now()->toIso8601String(),
            'acuse' => $this->generateMockAcuse($uuid),
        ]);
    }

    /**
     * Obtener estado de un CFDI
     */
    public function getStatus(string $uuid, PartnerEntity $entity): array
    {
        return [
            'uuid' => $uuid,
            'status' => 'Vigente',
            'cancellable' => true,
            'cancellation_status' => null,
            'provider' => 'MockPAC',
        ];
    }

    /**
     * Nombre del proveedor
     */
    public function getProviderName(): string
    {
        return 'MockPAC (Desarrollo)';
    }

    /**
     * Agregar timbre fiscal simulado al XML
     */
    protected function addMockTimbre(string $xml, string $uuid): string
    {
        $selloSAT = Str::random(64);
        $selloCFD = Str::random(64);
        $noCertificadoSAT = '20001000000300022323';
        $fechaTimbrado = now()->format('Y-m-d\TH:i:s');

        $timbre = <<<XML
<tfd:TimbreFiscalDigital
    xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital"
    xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/cfd/timbrefiscaldigital/TimbreFiscalDigitalv11.xsd"
    Version="1.1"
    UUID="{$uuid}"
    FechaTimbrado="{$fechaTimbrado}"
    RfcProvCertif="SPR190613I52"
    SelloCFD="{$selloCFD}"
    NoCertificadoSAT="{$noCertificadoSAT}"
    SelloSAT="{$selloSAT}"/>
XML;

        // Insertar el timbre antes del cierre del comprobante
        $xml = str_replace('</cfdi:Comprobante>', "<cfdi:Complemento>{$timbre}</cfdi:Complemento></cfdi:Comprobante>", $xml);

        return $xml;
    }

    /**
     * Generar acuse de cancelación simulado
     */
    protected function generateMockAcuse(string $uuid): string
    {
        $fecha = now()->format('Y-m-d\TH:i:s');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Acuse xmlns="http://cancelacfd.sat.gob.mx" Fecha="{$fecha}" RfcEmisor="MOCK" WorkProcessId="MOCK-WP-001">
    <Folios>
        <UUID>{$uuid}</UUID>
        <EstatusUUID>201</EstatusUUID>
    </Folios>
    <Signature>MOCK_SIGNATURE</Signature>
</Acuse>
XML;
    }
}
