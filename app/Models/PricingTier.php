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
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'min_monthly_purchases' => 'decimal:2',
        'max_monthly_purchases' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
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
     * Calcular precio con descuento de este tier
     */
    public function applyDiscount($price)
    {
        $discount = ($price * $this->discount_percentage) / 100;
        return $price - $discount;
    }
}