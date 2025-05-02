<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintecCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function providerCategories()
    {
        return $this->belongsToMany(ProductCategory::class, 'printec_category_product_category', 'printec_category_id', 'product_category_id');
    }
}
