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
        'is_active',
        'is_default',
        // Campos para CFDI
        'csd_cer_path',
        'csd_key_path',
        'csd_password',
        'csd_valid_from',
        'csd_valid_until',
        'fiscal_regime',
        'zip_code',
        'invoice_series',
        'invoice_next_folio',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'csd_valid_from' => 'date',
        'csd_valid_until' => 'date',
        'invoice_next_folio' => 'integer',
    ];

    protected $hidden = [
        'csd_password', // No exponer la contraseña en JSON
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

    /**
     * Relación con facturas
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
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
        return $this->bankAccounts()->where('is_primary', true)->first() 
            ?? $this->bankAccounts()->first();
    }

    /**
     * Obtener el conteo de cotizaciones
     */
    public function getQuotesCountAttribute()
    {
        return $this->quotes()->count();
    }

    // ========================================================================
    // MÉTODOS PARA CFDI
    // ========================================================================

    /**
     * Verificar si tiene certificados CSD configurados
     */
    public function hasCsdConfigured(): bool
    {
        return !empty($this->csd_cer_path)
            && !empty($this->csd_key_path)
            && !empty($this->csd_password);
    }

    /**
     * Verificar si los certificados CSD son válidos (no expirados)
     */
    public function isCsdValid(): bool
    {
        if (!$this->hasCsdConfigured()) {
            return false;
        }

        if ($this->csd_valid_until === null) {
            return true; // Si no hay fecha, asumimos válido
        }

        return $this->csd_valid_until->isFuture();
    }

    /**
     * Verificar si puede emitir facturas
     */
    public function canIssueInvoices(): bool
    {
        return $this->is_active
            && !empty($this->rfc)
            && !empty($this->razon_social)
            && !empty($this->fiscal_regime)
            && !empty($this->zip_code);
    }

    /**
     * Obtener la contraseña CSD desencriptada
     */
    public function getCsdPasswordDecrypted(): ?string
    {
        if (empty($this->csd_password)) {
            return null;
        }

        try {
            return decrypt($this->csd_password);
        } catch (\Exception $e) {
            return $this->csd_password; // Por si no está encriptada
        }
    }

    /**
     * Establecer la contraseña CSD encriptada
     */
    public function setCsdPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['csd_password'] = encrypt($value);
        } else {
            $this->attributes['csd_password'] = null;
        }
    }

    /**
     * Obtener el siguiente folio y serie formateado
     */
    public function getNextFolioFormatted(): string
    {
        return "{$this->invoice_series}-{$this->invoice_next_folio}";
    }

    /**
     * Obtener el label del régimen fiscal
     */
    public function getFiscalRegimeLabelAttribute(): string
    {
        $regimes = [
            '601' => 'General de Ley Personas Morales',
            '603' => 'Personas Morales con Fines no Lucrativos',
            '605' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606' => 'Arrendamiento',
            '607' => 'Régimen de Enajenación o Adquisición de Bienes',
            '608' => 'Demás ingresos',
            '610' => 'Residentes en el Extranjero sin Establecimiento Permanente en México',
            '611' => 'Ingresos por Dividendos (socios y accionistas)',
            '612' => 'Personas Físicas con Actividades Empresariales y Profesionales',
            '614' => 'Ingresos por intereses',
            '615' => 'Régimen de los ingresos por obtención de premios',
            '616' => 'Sin obligaciones fiscales',
            '620' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
            '621' => 'Incorporación Fiscal',
            '622' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623' => 'Opcional para Grupos de Sociedades',
            '624' => 'Coordinados',
            '625' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            '626' => 'Régimen Simplificado de Confianza',
        ];

        return $regimes[$this->fiscal_regime] ?? $this->fiscal_regime ?? 'No configurado';
    }
}