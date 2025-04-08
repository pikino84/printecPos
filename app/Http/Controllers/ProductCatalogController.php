<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;


class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category'])
            ->orderBy('name')
            ->take(120)
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function fetch(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%");
            });
        }

        $products = $query->orderBy('name')->paginate(20);

        return response()->json([
            'html' => view('products.partials.cards', compact('products'))->render(),
            'hasMore' => $products->hasMorePages(),
        ]);
    }
}