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

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
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
        $this->tax = $this->subtotal * $taxRate;
        
        $this->total = $this->subtotal + $this->tax;
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

    // =========================================================================
    // MÉTODOS PARA FACTURACIÓN
    // =========================================================================

    /**
     * Verificar si la cotización puede ser aceptada
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'sent' && !$this->isExpired();
    }

    /**
     * Verificar si se pueden generar facturas
     */
    public function canGenerateInvoices(): bool
    {
        return $this->status === 'accepted' && $this->items()->count() > 0;
    }

    /**
     * Verificar si ya tiene facturas generadas
     */
    public function hasInvoices(): bool
    {
        return $this->invoices()->exists();
    }

    /**
     * Obtener el total ya facturado
     */
    public function getInvoicedTotalAttribute(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', ['draft', 'stamped'])
            ->sum('total');
    }

    /**
     * Obtener el total pendiente de facturar
     */
    public function getPendingInvoiceTotalAttribute(): float
    {
        return max(0, $this->total - $this->invoiced_total);
    }

    /**
     * Verificar si está completamente facturada
     */
    public function isFullyInvoiced(): bool
    {
        return $this->pending_invoice_total <= 0;
    }

    /**
     * Aceptar la cotización
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
     * Rechazar la cotización
     */
    public function reject(): bool
    {
        if ($this->status !== 'sent') {
            return false;
        }

        $this->status = 'rejected';
        return $this->save();
    }
}