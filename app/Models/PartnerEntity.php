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
        // Configuración de urgencia
        'urgent_fee_percentage',
        'urgent_days_limit',
        // Configuración de correo
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'mail_from_address',
        'mail_from_name',
        'mail_cc_addresses',
        'mail_configured',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'mail_configured' => 'boolean',
        'smtp_port' => 'integer',
        'urgent_fee_percentage' => 'decimal:2',
        'urgent_days_limit' => 'integer',
    ];

    protected $hidden = [
        'smtp_password',
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
     * Obtener la cuenta bancaria en USD (si existe y es diferente a la principal)
     */
    public function getUsdBankAccount()
    {
        $mainAccount = $this->getMainBankAccount();

        // Buscar cuenta en USD que no sea la principal
        return $this->bankAccounts()
            ->where('currency', 'USD')
            ->where('is_active', true)
            ->when($mainAccount, function ($query) use ($mainAccount) {
                $query->where('id', '!=', $mainAccount->id);
            })
            ->first();
    }

    /**
     * Obtener el conteo de cotizaciones
     */
    public function getQuotesCountAttribute()
    {
        return $this->quotes()->count();
    }

    // ========================================================================
    // MÉTODOS DE CONFIGURACIÓN DE URGENCIA
    // ========================================================================

    /**
     * Verificar si tiene configuración de trabajo urgente
     */
    public function hasUrgentConfig()
    {
        return $this->urgent_fee_percentage !== null
            && $this->urgent_fee_percentage > 0
            && $this->urgent_days_limit !== null
            && $this->urgent_days_limit > 0;
    }

    /**
     * Calcular cargo por urgencia basado en un subtotal
     */
    public function calculateUrgencyFee($subtotal)
    {
        if (!$this->hasUrgentConfig()) {
            return 0;
        }

        return round($subtotal * ($this->urgent_fee_percentage / 100), 2);
    }

    // ========================================================================
    // MÉTODOS DE CONFIGURACIÓN DE CORREO
    // ========================================================================

    /**
     * Verificar si tiene configuración de correo completa
     */
    public function hasMailConfig()
    {
        return $this->mail_configured
            && $this->smtp_host
            && $this->smtp_port
            && $this->smtp_username
            && $this->smtp_password
            && $this->mail_from_address;
    }

    /**
     * Obtener array de correos CC
     */
    public function getMailCcArray()
    {
        if (empty($this->mail_cc_addresses)) {
            return [];
        }

        return array_map('trim', explode(',', $this->mail_cc_addresses));
    }

    /**
     * Encriptar contraseña SMTP antes de guardar
     */
    public function setSmtpPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['smtp_password'] = encrypt($value);
        }
    }

    /**
     * Desencriptar contraseña SMTP al obtener
     */
    public function getSmtpPasswordDecryptedAttribute()
    {
        if ($this->attributes['smtp_password'] ?? null) {
            try {
                return decrypt($this->attributes['smtp_password']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Obtener configuración de mailer para usar con Mail
     */
    public function getMailerConfig()
    {
        if (!$this->hasMailConfig()) {
            return null;
        }

        return [
            'transport' => 'smtp',
            'host' => $this->smtp_host,
            'port' => $this->smtp_port,
            'encryption' => $this->smtp_encryption === 'none' ? null : $this->smtp_encryption,
            'username' => $this->smtp_username,
            'password' => $this->smtp_password_decrypted,
            'from' => [
                'address' => $this->mail_from_address,
                'name' => $this->mail_from_name ?: $this->razon_social,
            ],
        ];
    }

    /**
     * Obtener badge de estado de correo
     */
    public function getMailStatusBadgeAttribute()
    {
        if ($this->hasMailConfig()) {
            return '<span class="badge bg-success">Configurado</span>';
        }
        return '<span class="badge bg-warning">No configurado</span>';
    }
}