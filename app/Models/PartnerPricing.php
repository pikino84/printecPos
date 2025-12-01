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
            // Fallback: si no hay tiers configurados, usar markup 52%
            return $basePrice * 1.52;
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
     */
    public function getPriceBreakdown($basePrice, $isPrintecProduct = true)
    {
        $tier = $this->getEffectiveTier();
        
        if (!$isPrintecProduct || !$tier) {
            return [
                'base_price' => $basePrice,
                'tier_name' => null,
                'markup_percentage' => 0,
                'discount_percentage' => 0,
                'after_markup' => $basePrice,
                'after_discount' => $basePrice,
                'cost_price' => $basePrice,
                'partner_markup' => $this->markup_percentage,
                'sale_price' => $this->applyMarkup($basePrice),
            ];
        }

        $afterMarkup = $tier->applyMarkup($basePrice);
        $afterDiscount = $tier->applyDiscount($afterMarkup);

        return [
            'base_price' => $basePrice,
            'tier_name' => $tier->name,
            'markup_percentage' => $tier->markup_percentage,
            'discount_percentage' => $tier->discount_percentage,
            'after_markup' => $afterMarkup,
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