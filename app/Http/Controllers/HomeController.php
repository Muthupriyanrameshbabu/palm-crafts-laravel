<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true)])
            ->limit(4)
            ->get();

        $featuredImage = $featuredProducts->first()?->images->first()?->url();

        return view('home', compact('featuredProducts', 'featuredImage'));
    }
}
