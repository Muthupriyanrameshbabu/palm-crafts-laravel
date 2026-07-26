<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function show()
    {
        $cart = $this->cartService->current()->load('items.product.images', 'items.variant');

        return view('cart.show', compact('cart'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $variant = isset($validated['variant_id']) ? ProductVariant::find($validated['variant_id']) : null;

        $this->cartService->add($product, $validated['quantity'], $variant);

        return back()->with('success', "{$product->name} added to your bag.");
    }

    public function update(Request $request, int $cartItem): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $this->cartService->updateQuantity($cartItem, $validated['quantity']);

        return back();
    }

    public function destroy(int $cartItem): RedirectResponse
    {
        $this->cartService->remove($cartItem);

        return back()->with('success', 'Item removed from your bag.');
    }
}
