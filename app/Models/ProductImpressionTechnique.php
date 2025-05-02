<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImpressionTechnique extends Model
{
    use HasFactory;

    protected $table = 'product_impression_technique';

    protected $fillable = [
        'product_id',
        'code',
        'name',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
