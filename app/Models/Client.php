<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'razon_social',
        'rfc',
        'direccion',
        'notas',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['nombre_completo'];

    // Mutator para convertir RFC a mayúsculas al guardar
    public function setRfcAttribute($value)
    {
        $this->attributes['rfc'] = $value ? strtoupper(trim($value)) : null;
    }

    // Relaciones
    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'client_partner')
            ->withPivot(['first_contact_at', 'notes'])
            ->withTimestamps();
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido);
    }

    // Scopes para búsquedas
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPartner(Builder $query, $partnerId)
    {
        return $query->whereHas('partners', function ($q) use ($partnerId) {
            $q->where('partner_id', $partnerId);
        });
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('apellido', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('rfc', 'like', "%{$search}%")
              ->orWhere('razon_social', 'like', "%{$search}%")
              ->orWhere('telefono', 'like', "%{$search}%");
        });
    }

    // Métodos auxiliares
    public function hasContactWith($partnerId)
    {
        return $this->partners()->where('partner_id', $partnerId)->exists();
    }

    public function addPartner($partnerId, $notes = null)
    {
        if (!$this->hasContactWith($partnerId)) {
            $this->partners()->attach($partnerId, [
                'first_contact_at' => now(),
                'notes' => $notes,
            ]);
        }
    }
}