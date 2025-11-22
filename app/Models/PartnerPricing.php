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
     * Aplicar markup del partner a un precio
     */
    public function applyMarkup($price)
    {
        $markup = ($price * $this->markup_percentage) / 100;
        return $price + $markup;
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
     * Calcular precio final para este partner (sin IVA)
     */
    public function calculatePrice($basePrice, $isPrintecProduct = true)
    {
        // Si es producto propio del partner, solo aplicar su markup
        if (!$isPrintecProduct) {
            return $this->applyMarkup($basePrice);
        }

        // Producto de Printec o proveedores
        $printecMarkup = PricingSetting::get('printec_markup', 52);
        
        // 1. Aplicar markup de Printec
        $priceWithPrintecMarkup = $basePrice + ($basePrice * $printecMarkup / 100);
        
        // 2. Aplicar descuento por tier
        $priceAfterDiscount = $priceWithPrintecMarkup;
        if ($this->currentTier) {
            $priceAfterDiscount = $this->currentTier->applyDiscount($priceWithPrintecMarkup);
        }
        
        // 3. Aplicar markup del partner
        $finalPrice = $this->applyMarkup($priceAfterDiscount);
        
        return $finalPrice;
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
}