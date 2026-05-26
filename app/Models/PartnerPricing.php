<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPricing extends Model
{
    use HasFactory;

    protected $table = 'partner_pricing';

    protected $fillable = [
        'partner_id',
        'markup_percentage',
        'api_markup_percentage',
        'current_tier_id',
        'api_tier_id',
        'last_month_purchases',
        'current_month_purchases',
        'tier_assigned_at',
        'manual_tier_override',
    ];

    protected $casts = [
        'markup_percentage' => 'decimal:2',
        'api_markup_percentage' => 'decimal:2',
        'last_month_purchases' => 'decimal:2',
        'current_month_purchases' => 'decimal:2',
        'tier_assigned_at' => 'date',
        'manual_tier_override' => 'boolean',
    ];

    // ========================================================================
    // RELACIONES
    // ========================================================================

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function currentTier()
    {
        return $this->belongsTo(PricingTier::class, 'current_tier_id');
    }

    /**
     * Nivel propio del widget/API. NULL = hereda el del catálogo (currentTier).
     */
    public function apiTier()
    {
        return $this->belongsTo(PricingTier::class, 'api_tier_id');
    }

    // ========================================================================
    // MÉTODOS
    // ========================================================================

    /**
     * Obtener el tier actual o, si no tiene, el nivel público (primero por orden).
     */
    public function getEffectiveTier()
    {
        return $this->currentTier ?? PricingTier::publicTier();
    }

    /**
     * Nivel que realmente aplica al partner al cotizar.
     *
     * @param  bool  $forApi  Contexto widget/API: usa api_tier_id si está
     *                        configurado; si es NULL hereda el del catálogo.
     *
     * Épica 05: un Asociado puro con perfil < 100% pierde su nivel de
     * distribuidor y cotiza al nivel público, tanto en el POS como en el
     * widget. Mixto y Proveedor conservan su nivel por ser socios
     * estratégicos que también proveen producto.
     */
    protected function resolvePricingTier(bool $forApi = false)
    {
        if ($this->shouldChargePublicPrice()) {
            return PricingTier::publicTier();
        }

        if ($forApi && $this->api_tier_id !== null) {
            return $this->apiTier;
        }

        return $this->getEffectiveTier();
    }

    /**
     * % de ganancia que aplica según el contexto.
     * En el widget/API usa api_markup_percentage; si es NULL hereda el del catálogo.
     */
    public function effectiveMarkup(bool $forApi = false)
    {
        if ($forApi && $this->api_markup_percentage !== null) {
            return (float) $this->api_markup_percentage;
        }

        return (float) $this->markup_percentage;
    }

    /**
     * Aplicar markup del partner a un precio
     */
    public function applyMarkup($price, bool $forApi = false)
    {
        return $price * (1 + $this->effectiveMarkup($forApi) / 100);
    }

    /**
     * Agregar compra al mes actual
     */
    public function addPurchase($amount)
    {
        $this->current_month_purchases += $amount;
        $this->save();
    }

    /**
     * Calcular precio de costo para el partner (sin IVA, sin markup del partner)
     * Este es el precio que el partner paga a Printec
     * Fórmula: (Price + Markup del Tier) - Descuento del Tier
     * Nota: Todos los productos (propios y de proveedores) reciben los mismos aumentos
     *
     * Épica 05: si el partner es Asociado puro y su perfil < 100%, pierde su nivel de
     * distribuidor y cotiza al nivel público (el de entrada, primero por orden).
     * Mixto y Proveedor están exentos por ser socios estratégicos que también proveen producto.
     */
    public function calculateCostPrice($basePrice, $isPrintecProduct = true)
    {
        return $this->costPriceForTier($this->resolvePricingTier(), $basePrice);
    }

    /**
     * Precio de costo para el widget/API (usa el nivel propio del API o lo hereda).
     */
    public function calculateApiCostPrice($basePrice)
    {
        return $this->costPriceForTier($this->resolvePricingTier(true), $basePrice);
    }

    /**
     * Núcleo de cálculo de costo dado un nivel ya resuelto.
     * Sin nivel: cae al Markup Printec global.
     */
    protected function costPriceForTier($tier, $basePrice)
    {
        if (! $tier) {
            $printecMarkup = PricingSetting::get('printec_markup', 52);

            return $basePrice * (1 + $printecMarkup / 100);
        }

        return $tier->calculatePrice($basePrice);
    }

    /**
     * El distribuidor pierde el descuento solo si es Asociado puro con perfil incompleto.
     * Mixto, Proveedor y Printec están exentos.
     */
    public function shouldChargePublicPrice(): bool
    {
        $partner = $this->partner;

        if (! $partner || ! $partner->isAsociado()) {
            return false;
        }

        return ! $partner->hasCompleteProfile();
    }

    /**
     * Calcular precio de venta sugerido (con markup del partner, sin IVA)
     * Este es el precio que el partner puede cobrar a su cliente
     */
    public function calculateSalePrice($basePrice, $isPrintecProduct = true)
    {
        $costPrice = $this->calculateCostPrice($basePrice, $isPrintecProduct);

        return $this->applyMarkup($costPrice);
    }

    /**
     * Precio de venta sugerido para el widget/API (sin IVA).
     * Usa el nivel y markup propios del API; cada uno hereda del catálogo si es NULL.
     */
    public function calculateApiSalePrice($basePrice)
    {
        $costPrice = $this->calculateApiCostPrice($basePrice);

        return $this->applyMarkup($costPrice, true);
    }

    /**
     * Calcular precio de costo con IVA
     */
    public function calculateCostPriceWithTax($basePrice, $isPrintecProduct = true, $taxRate = 16)
    {
        $costPrice = $this->calculateCostPrice($basePrice, $isPrintecProduct);

        return $costPrice * (1 + $taxRate / 100);
    }

    /**
     * Calcular precio de venta con IVA
     */
    public function calculateSalePriceWithTax($basePrice, $isPrintecProduct = true, $taxRate = 16)
    {
        $salePrice = $this->calculateSalePrice($basePrice, $isPrintecProduct);

        return $salePrice * (1 + $taxRate / 100);
    }

    /**
     * Obtener el desglose de precios para mostrar en UI
     * Fórmula: ((Base + Markup Printec) + Markup Tier) - Descuento Tier + Markup Partner
     * Nota: Todos los productos (propios y de proveedores) reciben los mismos aumentos
     */
    public function getPriceBreakdown($basePrice, $isPrintecProduct = true)
    {
        $printecMarkup = PricingSetting::get('printec_markup', 52);
        // Perfil incompleto (Épica 05): el nivel resuelto será el público.
        $blocked = $this->shouldChargePublicPrice();
        $tier = $this->resolvePricingTier();

        // Sin niveles configurados: solo Markup Printec global.
        if (! $tier) {
            $afterPrintecMarkup = $basePrice * (1 + $printecMarkup / 100);

            return [
                'base_price' => $basePrice,
                'tier_name' => null,
                'printec_markup' => $printecMarkup,
                'tier_markup' => 0,
                'discount_percentage' => 0,
                'after_printec_markup' => $afterPrintecMarkup,
                'after_tier_markup' => $afterPrintecMarkup,
                'after_discount' => $afterPrintecMarkup,
                'cost_price' => $afterPrintecMarkup,
                'partner_markup' => $this->markup_percentage,
                'sale_price' => $this->applyMarkup($afterPrintecMarkup),
                'profile_blocked' => $blocked,
            ];
        }

        // 1. Aplicar Markup Printec
        $afterPrintecMarkup = $tier->applyPrintecMarkup($basePrice);
        // 2. Aplicar Markup del Tier
        $afterTierMarkup = $tier->applyTierMarkup($afterPrintecMarkup);
        // 3. Aplicar Descuento del Tier
        $afterDiscount = $tier->applyDiscount($afterTierMarkup);

        return [
            'base_price' => $basePrice,
            'tier_name' => $tier->name,
            'printec_markup' => $printecMarkup,
            'tier_markup' => $tier->markup_percentage,
            'discount_percentage' => $tier->discount_percentage,
            'after_printec_markup' => $afterPrintecMarkup,
            'after_tier_markup' => $afterTierMarkup,
            'after_discount' => $afterDiscount,
            'cost_price' => $afterDiscount,
            'partner_markup' => $this->markup_percentage,
            'sale_price' => $this->applyMarkup($afterDiscount),
            'profile_blocked' => $blocked,
        ];
    }

    /**
     * Obtener o crear pricing para un partner
     */
    public static function getOrCreateForPartner($partnerId)
    {
        return self::firstOrCreate(
            ['partner_id' => $partnerId],
            ['markup_percentage' => 0]
        );
    }

    // ========================================================================
    // MÉTODOS LEGACY (mantener compatibilidad)
    // ========================================================================

    /**
     * @deprecated Usar calculateCostPrice() o calculateSalePrice()
     */
    public function calculatePrice($basePrice, $isPrintecProduct = true)
    {
        // Mantener compatibilidad: retorna precio de venta con markup del partner
        return $this->calculateSalePrice($basePrice, $isPrintecProduct);
    }
}
