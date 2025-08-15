<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PartnerEntityBankAccount;


class PartnerEntity extends Model
{
    use HasFactory;

    // Tabla: partner_entities (por convención no hace falta especificarla)
    protected $fillable = [
        'partner_id',
        'razon_social',
        'rfc',
        'telefono',
        'correo_contacto',
        'direccion',
        'logo',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(PartnerEntityBankAccount::class);
    }

    // Relación cómoda a la cuenta bancaria marcada como default
    public function defaultBankAccount()
    {
        return $this->hasOne(PartnerEntityBankAccount::class)
            ->where('is_default', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes / Accesores útiles
    |--------------------------------------------------------------------------
    */

    // Accesor: $entity->default_bank_account (con fallback a la primera)
    public function getDefaultBankAccountAttribute()
    {
        return $this->defaultBankAccount()->first() ?: $this->bankAccounts()->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Reglas de integridad: solo una "is_default" por partner
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::saving(function (self $entity) {
            // Si marcan esta entidad como default, apaga las demás del mismo partner
            if ($entity->is_default && $entity->partner_id) {
                static::where('partner_id', $entity->partner_id)
                    ->where('id', '!=', $entity->id ?? 0)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }
}
