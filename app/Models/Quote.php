<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'partner_id',
        'partner_entity_id',
        'client_id',
        'client_email',
        'client_name',
        'client_rfc',
        'client_razon_social',
        'quote_number',
        'status',
        'subtotal',
        'tax',
        'total',
        'is_urgent',
        'urgency_fee',
        'urgency_percentage',
        'notes',
        'customer_notes',
        'short_description',
        'valid_until',
        'sent_at',
        'sent_to_email',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'is_urgent' => 'boolean',
        'urgency_fee' => 'decimal:2',
        'urgency_percentage' => 'decimal:2',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function partnerEntity()
    {
        return $this->belongsTo(PartnerEntity::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Métodos auxiliares
    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('subtotal');

        // Obtener tasa de IVA desde settings
        $taxRate = \App\Models\PricingSetting::get('tax_rate', 16) / 100;

        // Calcular IVA sobre subtotal + cargo por urgencia
        $baseForTax = $this->subtotal + ($this->urgency_fee ?? 0);
        $this->tax = $baseForTax * $taxRate;

        $this->total = $this->subtotal + ($this->urgency_fee ?? 0) + $this->tax;
        $this->save();
    }

    public static function generateQuoteNumber()
    {
        $year = now()->year;
        $lastQuote = self::where('quote_number', 'like', "COT-{$year}-%")
            ->orderBy('quote_number', 'desc')
            ->first();

        if ($lastQuote) {
            $lastNumber = (int) substr($lastQuote->quote_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "COT-{$year}-{$newNumber}";
    }

    public function isExpired()
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft']);
    }

    public function canBeSent()
    {
        return $this->status === 'draft' && $this->items()->count() > 0;
    }

    public function getEffectiveClientEmail()
    {
        if ($this->client_id && $this->client) {
            return $this->client->email;
        }
        return $this->client_email;
    }

    /**
     * Verificar si la cotización puede ser aceptada
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'sent' && !$this->isExpired();
    }

    /**
     * Verificar si la cotización puede ser rechazada
     */
    public function canBeRejected(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Verificar si la cotización puede ser facturada
     */
    public function canBeInvoiced(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Verificar si la cotización puede ser expirada manualmente
     */
    public function canBeExpired(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Aceptar cotización
     */
    public function accept(): bool
    {
        if (!$this->canBeAccepted()) {
            return false;
        }

        $this->status = 'accepted';
        return $this->save();
    }

    /**
     * Rechazar cotización
     */
    public function reject(): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        $this->status = 'rejected';
        return $this->save();
    }

    /**
     * Marcar cotización como facturada
     */
    public function invoice(): bool
    {
        if (!$this->canBeInvoiced()) {
            return false;
        }

        $this->status = 'invoiced';
        return $this->save();
    }

    /**
     * Marcar cotización como expirada manualmente
     */
    public function markAsExpired(): bool
    {
        if (!$this->canBeExpired()) {
            return false;
        }

        $this->status = 'expired';
        return $this->save();
    }

    /**
     * Obtener el nombre del estado en español
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'sent' => 'Enviada',
            'accepted' => 'Aceptada',
            'rejected' => 'Rechazada',
            'expired' => 'Expirada',
            'invoiced' => 'Facturada',
            default => $this->status,
        };
    }
}