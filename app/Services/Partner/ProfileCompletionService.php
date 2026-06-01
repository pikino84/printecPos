<?php

namespace App\Services\Partner;

use App\Models\Partner;
use App\Models\PartnerEntity;

/**
 * Calcula el % de completitud del perfil de un Partner según los pesos
 * acordados con Eduardo el 2026-05-08:
 *   Fiscal 40 / Bancario 30 / Contacto 20 / Logo 10
 *
 * El cálculo es "todo o nada" por bloque: si falta un solo campo del bloque,
 * el bloque completo aporta 0%. Esto es deliberado — la épica busca empujar
 * a los distribuidores a completar bloques enteros, no a parchar campos.
 */
class ProfileCompletionService
{
    /**
     * Umbral mínimo de completitud para considerar el perfil "completo":
     * a partir de aquí el Asociado conserva su descuento, no se vetea y se
     * levanta el veto si lo tenía. Bajado de 100 a 70 el 2026-06-01 —
     * basta con cubrir Fiscal (40) + Bancario (30) para llegar al umbral.
     */
    public const COMPLETION_THRESHOLD = 70;

    public const WEIGHT_FISCAL = 40;

    public const WEIGHT_BANK = 30;

    public const WEIGHT_CONTACT = 20;

    public const WEIGHT_LOGO = 10;

    public const FISCAL_FIELDS = ['rfc', 'razon_social', 'direccion', 'telefono'];

    public const BANK_FIELDS = ['bank_name', 'account_holder', 'clabe'];

    public const CONTACT_FIELDS = ['contact_name', 'contact_phone', 'contact_email', 'direccion'];

    public function percentageFor(Partner $partner): int
    {
        $score = 0;

        if ($this->fiscalComplete($partner)) {
            $score += self::WEIGHT_FISCAL;
        }
        if ($this->bankComplete($partner)) {
            $score += self::WEIGHT_BANK;
        }
        if ($this->contactComplete($partner)) {
            $score += self::WEIGHT_CONTACT;
        }
        if ($this->logoComplete($partner)) {
            $score += self::WEIGHT_LOGO;
        }

        return $score;
    }

    /**
     * Devuelve los faltantes agrupados por bloque. Útil para correo y UI.
     *
     * @return array{fiscal: array<int,string>, bank: array<int,string>, contact: array<int,string>, logo: array<int,string>}
     */
    public function missingFieldsFor(Partner $partner): array
    {
        return [
            'fiscal' => $this->missingFiscalFields($partner),
            'bank' => $this->missingBankFields($partner),
            'contact' => $this->missingContactFields($partner),
            'logo' => $this->logoComplete($partner) ? [] : ['logo'],
        ];
    }

    public function isComplete(Partner $partner): bool
    {
        return $this->percentageFor($partner) >= self::COMPLETION_THRESHOLD;
    }

    private function fiscalComplete(Partner $partner): bool
    {
        return count($this->missingFiscalFields($partner)) === 0;
    }

    private function bankComplete(Partner $partner): bool
    {
        return count($this->missingBankFields($partner)) === 0;
    }

    private function contactComplete(Partner $partner): bool
    {
        return count($this->missingContactFields($partner)) === 0;
    }

    private function logoComplete(Partner $partner): bool
    {
        // El logo cuenta tanto si está en el branding del sitio (partners.logo)
        // como si el distribuidor ya lo subió en su razón social (entity.logo_path).
        if (! empty($partner->logo)) {
            return true;
        }

        $entity = $this->primaryEntity($partner);

        return $entity !== null && ! empty($entity->logo_path);
    }

    /**
     * @return array<int,string>
     */
    private function missingFiscalFields(Partner $partner): array
    {
        $entity = $this->primaryEntity($partner);

        if (! $entity) {
            return self::FISCAL_FIELDS;
        }

        return array_values(array_filter(self::FISCAL_FIELDS, fn ($field) => empty($entity->{$field})));
    }

    /**
     * @return array<int,string>
     */
    private function missingBankFields(Partner $partner): array
    {
        $entity = $this->primaryEntity($partner);

        if (! $entity) {
            return self::BANK_FIELDS;
        }

        $account = $entity->bankAccounts()
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if (! $account) {
            return self::BANK_FIELDS;
        }

        return array_values(array_filter(self::BANK_FIELDS, fn ($field) => empty($account->{$field})));
    }

    /**
     * @return array<int,string>
     */
    private function missingContactFields(Partner $partner): array
    {
        return array_values(array_filter(self::CONTACT_FIELDS, fn ($field) => empty($partner->{$field})));
    }

    private function primaryEntity(Partner $partner): ?PartnerEntity
    {
        if ($partner->default_entity_id) {
            return $partner->defaultEntity;
        }

        return $partner->entities()->orderBy('id')->first();
    }
}
