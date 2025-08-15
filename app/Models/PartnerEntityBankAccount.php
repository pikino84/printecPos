<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerEntityBankAccount extends Model
{
    use HasFactory;

    // Tabla por convención: partner_entity_bank_accounts

    protected $fillable = [
        'partner_entity_id',
        'bank_name',
        'alias',
        'account_holder',
        'account_number',
        'clabe',
        'swift',
        'iban',
        'currency',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function entity()
    {
        return $this->belongsTo(PartnerEntity::class, 'partner_entity_id');
    }
}