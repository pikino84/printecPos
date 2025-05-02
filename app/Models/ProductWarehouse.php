<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'codigo',
        'name',
        'nickname',
    ];

    public function provider()
    {
        return $this->belongsTo(ProductProvider::class, 'provider_id');
    }
}
