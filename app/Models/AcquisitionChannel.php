<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcquisitionChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // ========================================================================
    // RELACIONES
    // ========================================================================

    /**
     * Clientes que llegaron por este canal
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Partners que llegaron por este canal
     */
    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    // ========================================================================
    // SCOPES
    // ========================================================================

    /**
     * Scope para canales activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para ordenar por campo order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ========================================================================
    // MÉTODOS AUXILIARES
    // ========================================================================

    /**
     * Obtener el conteo de clientes
     */
    public function getClientsCountAttribute()
    {
        return $this->clients()->count();
    }

    /**
     * Obtener el conteo de partners
     */
    public function getPartnersCountAttribute()
    {
        return $this->partners()->count();
    }

    /**
     * Obtener estadísticas del canal
     */
    public function getStats()
    {
        return [
            'clients_count' => $this->clients()->count(),
            'partners_count' => $this->partners()->count(),
            'total' => $this->clients()->count() + $this->partners()->count(),
        ];
    }
}