<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PartnerEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'rfc',
        'razon_social',
        'direccion',
        'telefono',
        'correo_contacto',
        'logo_path',
        'payment_terms',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // ========================================================================
    // RELACIONES
    // ========================================================================
    
    /**
     * Relación con el partner
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Relación con las cuentas bancarias
     */
    public function bankAccounts()
    {
        return $this->hasMany(PartnerEntityBankAccount::class);
    }

    /**
     * Relación con cotizaciones
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'partner_entity_id');
    }

    // ========================================================================
    // SCOPES
    // ========================================================================
    
    /**
     * Scope para obtener solo entidades activas
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por partner
     */
    public function scopeForPartner(Builder $query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    /**
     * Scope para buscar entidades
     */
    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('rfc', 'like', "%{$search}%")
              ->orWhere('razon_social', 'like', "%{$search}%");
        });
    }

    // ========================================================================
    // ACCESSORS Y MUTATORS
    // ========================================================================
    
    /**
     * Mutator para convertir RFC a mayúsculas
     */
    public function setRfcAttribute($value)
    {
        $this->attributes['rfc'] = $value ? strtoupper(trim($value)) : null;
    }

    /**
     * Accessor para obtener nombre completo (entidad o razón social)
     */
    public function getFullNameAttribute()
    {
        return $this->razon_social ?: $this->name;
    }

    /**
     * Accessor para obtener el badge de estado
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Activa</span>'
            : '<span class="badge bg-danger">Inactiva</span>';
    }

    // ========================================================================
    // MÉTODOS AUXILIARES
    // ========================================================================
    
    /**
     * Verificar si tiene cuentas bancarias
     */
    public function hasBankAccounts()
    {
        return $this->bankAccounts()->exists();
    }

    /**
     * Obtener la cuenta bancaria principal
     */
    public function getMainBankAccount()
    {
        return $this->bankAccounts()->where('is_default', true)->first()
            ?? $this->bankAccounts()->first();
    }

    /**
     * Obtener el conteo de cotizaciones
     */
    public function getQuotesCountAttribute()
    {
        return $this->quotes()->count();
    }
}