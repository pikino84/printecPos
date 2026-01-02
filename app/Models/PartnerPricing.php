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
        'current_tier_id',
        'last_month_purchases',
        'current_month_purchases',
        'tier_assigned_at',
        'manual_tier_override',
    ];

    protected $casts = [
        'markup_percentage' => 'decimal:2',
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

    // ========================================================================
    // MÉTODOS
    // ========================================================================

    /**
     * Obtener el tier actual o el tier por defecto (Junior)
     */
    public function getEffectiveTier()
    {
        if ($this->currentTier) {
            return $this->currentTier;
        }

        // Si no tiene tier asignado, usar Junior (el primero por orden)
        return PricingTier::active()->ordered()->first();
    }

    /**
     * Aplicar markup del partner a un precio
     */
    public function applyMarkup($price)
    {
        return $price * (1 + $this->markup_percentage / 100);
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
     */
    public function calculateCostPrice($basePrice, $isPrintecProduct = true)
    {
        // Si es producto propio del partner, el costo es el precio base
        if (!$isPrintecProduct) {
            return $basePrice;
        }

        // Producto de Printec o proveedores: aplicar tier
        $tier = $this->getEffectiveTier();

        if (!$tier) {
            // Fallback: si no hay tiers configurados, usar markup Printec de settings
            $printecMarkup = PricingSetting::get('printec_markup', 52);
            return $basePrice * (1 + $printecMarkup / 100);
        }

        // Aplicar markup y descuento del tier
        return $tier->calculatePrice($basePrice);
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
     */
    public function getPriceBreakdown($basePrice, $isPrintecProduct = true)
    {
        $tier = $this->getEffectiveTier();
        $printecMarkup = PricingSetting::get('printec_markup', 52);

        if (!$isPrintecProduct) {
            return [
                'base_price' => $basePrice,
                'tier_name' => null,
                'printec_markup' => 0,
                'tier_markup' => 0,
                'discount_percentage' => 0,
                'after_printec_markup' => $basePrice,
                'after_tier_markup' => $basePrice,
                'after_discount' => $basePrice,
                'cost_price' => $basePrice,
                'partner_markup' => $this->markup_percentage,
                'sale_price' => $this->applyMarkup($basePrice),
            ];
        }

        if (!$tier) {
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