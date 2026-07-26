<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Category $category = null)
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->where('is_primary', true)])
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->latest()
            ->paginate(12);

        $categories = Category::orderBy('sort_order')->get();

        return view('products.index', compact('products', 'categories', 'category'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'variants' => fn ($q) => $q->where('is_active', true)]);

        return view('products.show', compact('product'));
    }
}
