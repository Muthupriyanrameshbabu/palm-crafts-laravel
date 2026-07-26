<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Fetch the current user's or guest's cart, creating one if it doesn't exist yet.
     * Guest carts are keyed by session ID; on login, call mergeGuestCartIntoUser().
     */
    public function current(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $sessionId = Session::getId();

        return Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }

    /**
     * Add a product (optionally a specific variant) to the cart.
     * Validates stock availability before writing, and locks the row to prevent
     * race conditions from two simultaneous requests overselling the last unit.
     *
     * @throws ValidationException if requested quantity exceeds available stock
     */
    public function add(Product $product, int $quantity, ?ProductVariant $variant = null): Cart
    {
        return \DB::transaction(function () use ($product, $quantity, $variant) {
            $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;

            $cart = $this->current();
            $existing = $cart->items()
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->lockForUpdate()
                ->first();

            $requestedTotal = $quantity + ($existing->quantity ?? 0);

            if ($requestedTotal > $availableStock) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$availableStock} unit(s) of this item are in stock.",
                ]);
            }

            $unitPrice = $variant ? $variant->priceInPaise() : $product->price_in_paise;

            if ($existing) {
                $existing->update(['quantity' => $requestedTotal]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $quantity,
                    'unit_price_in_paise' => $unitPrice,
                ]);
            }

            return $cart->fresh('items.product', 'items.variant');
        });
    }

    public function updateQuantity(int $cartItemId, int $quantity): Cart
    {
        $cart = $this->current();
        $item = $cart->items()->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        return $cart->fresh('items.product', 'items.variant');
    }

    public function remove(int $cartItemId): Cart
    {
        $cart = $this->current();
        $cart->items()->where('id', $cartItemId)->delete();

        return $cart->fresh('items.product', 'items.variant');
    }

    /**
     * Called on login: attaches the guest session's cart items to the now-authenticated
     * user, merging quantities if the user already had an existing cart with the same item.
     */
    public function mergeGuestCartIntoUser(string $guestSessionId, int $userId): void
    {
        $guestCart = Cart::where('session_id', $guestSessionId)->whereNull('user_id')->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);

        foreach ($guestCart->items as $item) {
            $existing = $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                $item->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
    }
}
