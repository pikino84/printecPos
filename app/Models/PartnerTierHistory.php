<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerTierHistory extends Model
{
    use HasFactory;

    protected $table = 'partner_tier_history';

    protected $fillable = [
        'partner_id',
        'tier_id',
        'purchases_amount',
        'period_start',
        'period_end',
        'is_manual',
        'notes',
    ];

    protected $casts = [
        'purchases_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'is_manual' => 'boolean',
    ];

    // ========================================================================
    // RELACIONES
    // ========================================================================

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function tier()
    {
        return $this->belongsTo(PricingTier::class);
    }

    // ========================================================================
    // SCOPES
    // ========================================================================

    public function scopeForPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->where('period_start', $start)
            ->where('period_end', $end);
    }

    public function scopeRecent($query, $limit = 12)
    {
        return $query->orderBy('period_start', 'desc')->limit($limit);
    }

    // ========================================================================
    // MÉTODOS
    // ========================================================================

    /**
     * Crear registro de historial para un partner
     */
    public static function recordTierAssignment(
        $partnerId,
        $tierId,
        $purchasesAmount,
        $periodStart,
        $periodEnd,
        $isManual = false,
        $notes = null
    ) {
        return self::create([
            'partner_id' => $partnerId,
            'tier_id' => $tierId,
            'purchases_amount' => $purchasesAmount,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'is_manual' => $isManual,
            'notes' => $notes,
        ]);
    }
}