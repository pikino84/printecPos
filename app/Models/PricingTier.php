<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'min_monthly_purchases',
        'max_monthly_purchases',
        'discount_percentage',
        'markup_percentage',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'min_monthly_purchases' => 'decimal:2',
        'max_monthly_purchases' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    // ========================================================================
    // RELACIONES
    // ========================================================================

    /**
     * Partners que tienen este tier actualmente
     */
    public function partners()
    {
        return $this->hasMany(PartnerPricing::class, 'current_tier_id');
    }

    /**
     * Historial de asignaciones de este tier
     */
    public function history()
    {
        return $this->hasMany(PartnerTierHistory::class, 'tier_id');
    }

    // ========================================================================
    // SCOPES
    // ========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('min_monthly_purchases');
    }

    // ========================================================================
    // MÉTODOS
    // ========================================================================

    /**
     * Determinar si un monto de compras califica para este tier
     */
    public function qualifiesForAmount($amount)
    {
        $amount = (float) $amount;
        
        if ($amount < $this->min_monthly_purchases) {
            return false;
        }
        
        if ($this->max_monthly_purchases && $amount > $this->max_monthly_purchases) {
            return false;
        }
        
        return true;
    }

    /**
     * Obtener el tier apropiado para un monto de compras
     */
    public static function getTierForAmount($amount)
    {
        return self::active()
            ->ordered()
            ->get()
            ->first(function($tier) use ($amount) {
                return $tier->qualifiesForAmount($amount);
            });
    }

    /**
     * Aplicar Markup Printec (de /pricing-settings)
     */
    public function applyPrintecMarkup($price)
    {
        $printecMarkup = PricingSetting::get('printec_markup', 52);
        return $price * (1 + $printecMarkup / 100);
    }

    /**
     * Aplicar markup del tier al precio
     */
    public function applyTierMarkup($price)
    {
        return $price * (1 + $this->markup_percentage / 100);
    }

    /**
     * Aplicar descuento del tier
     */
    public function applyDiscount($price)
    {
        return $price * (1 - $this->discount_percentage / 100);
    }

    /**
     * Calcular precio final con todos los markups y descuento (sin IVA)
     * Fórmula: ((Price + Markup Printec) + Markup Tier) - Descuento Tier
     */
    public function calculatePrice($basePrice)
    {
        // 1. Aplicar Markup Printec
        $withPrintecMarkup = $this->applyPrintecMarkup($basePrice);
        // 2. Aplicar Markup del Tier
        $withTierMarkup = $this->applyTierMarkup($withPrintecMarkup);
        // 3. Aplicar Descuento del Tier
        $afterDiscount = $this->applyDiscount($withTierMarkup);

        return $afterDiscount;
    }

    /**
     * Calcular precio final con markup, descuento e IVA
     */
    public function calculatePriceWithTax($basePrice, $taxRate = 16)
    {
        $priceBeforeTax = $this->calculatePrice($basePrice);
        
        return $priceBeforeTax * (1 + $taxRate / 100);
    }

    /**
     * Obtener la fórmula legible del tier
     */
    public function getFormulaAttribute()
    {
        if ($this->discount_percentage > 0) {
            return "(Price +{$this->markup_percentage}%) - {$this->discount_percentage}% + IVA";
        }
        
        return "Price + {$this->markup_percentage}% + IVA";
    }
}