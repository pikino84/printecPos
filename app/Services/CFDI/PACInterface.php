<?php

namespace App\Services\CFDI;

use App\Models\PartnerEntity;

interface PACInterface
{
    /**
     * Timbrar un XML de CFDI
     */
    public function stamp(string $xml, PartnerEntity $entity): StampResult;

    /**
     * Cancelar un CFDI timbrado
     */
    public function cancel(
        string $uuid,
        PartnerEntity $entity,
        string $reason,
        ?string $replacementUuid = null
    ): CancelResult;

    /**
     * Verificar el estado de un CFDI
     */
    public function getStatus(string $uuid, PartnerEntity $entity): array;

    /**
     * Obtener el nombre del proveedor
     */
    public function getProviderName(): string;
}
