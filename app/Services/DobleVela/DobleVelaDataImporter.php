<?php

namespace App\Services\DobleVela;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductProvider;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Warehouse;

class DobleVelaDataImporter
{
    public function import(): void
    {
        $json = Storage::get('doblevela/products.json');
        $data = json_decode($json, true);
        

        
    }
}
