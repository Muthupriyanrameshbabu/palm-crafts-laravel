<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Converts a cart into a pending order. Stock is re-validated and locked here
     * (not just at add-to-cart time) because time may have passed and stock may
     * have sold out to another customer since items were added.
     *
     * Stock is NOT deducted yet — that happens only after payment is confirmed,
     * via confirmPayment(), so abandoned/failed payments don't hold stock hostage.
     */
    public function createFromCart(Cart $cart, Address $shippingAddress, string $email, string $phone): Order
    {
        return DB::transaction(function () use ($cart, $shippingAddress, $email, $phone) {
            $cart->load('items.product', 'items.variant');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            foreach ($cart->items as $item) {
                $available = $item->variant ? $item->variant->stock_quantity : $item->product->stock_quantity;

                if ($item->quantity > $available) {
                    throw ValidationException::withMessages([
                        'cart' => "\"{$item->product->name}\" only has {$available} unit(s) left in stock.",
                    ]);
                }
            }

            $subtotal = $cart->subtotalInPaise();
            $shippingCost = $this->calculateShipping($subtotal);
            $tax = 0; // Set this if/when GST registration applies to the store.
            $total = $subtotal + $shippingCost + $tax;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $cart->user_id,
                'email' => $email,
                'phone' => $phone,
                'shipping_address_id' => $shippingAddress->id,
                'shipping_address_snapshot' => $shippingAddress->only([
                    'full_name', 'phone', 'line_1', 'line_2', 'city', 'state', 'postal_code', 'country',
                ]),
                'subtotal_in_paise' => $subtotal,
                'shipping_in_paise' => $shippingCost,
                'tax_in_paise' => $tax,
                'total_in_paise' => $total,
                'status' => 'pending_payment',
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'quantity' => $item->quantity,
                    'unit_price_in_paise' => $item->unit_price_in_paise,
                    'line_total_in_paise' => $item->lineTotalInPaise(),
                ]);
            }

            return $order;
        });
    }

    /**
     * Called only after Razorpay signature verification succeeds. Deducts stock
     * and empties the cart. Wrapped in a transaction with row locks so concurrent
     * webhook + redirect callbacks can't double-deduct stock for the same order.
     */
    public function confirmPayment(Order $order, string $razorpayPaymentId, string $razorpaySignature): void
    {
        DB::transaction(function () use ($order, $razorpayPaymentId, $razorpaySignature) {
            $order = Order::where('id', $order->id)->lockForUpdate()->first();

            if ($order->status === 'paid') {
                return; // Already processed — webhook and redirect can both fire for the same payment.
            }

            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    \App\Models\ProductVariant::where('id', $item->product_variant_id)
                        ->decrement('stock_quantity', $item->quantity);
                } else {
                    \App\Models\Product::where('id', $item->product_id)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }

            $order->markAsPaid($razorpayPaymentId, $razorpaySignature);

            if ($order->user_id) {
                Cart::where('user_id', $order->user_id)->first()?->items()->delete();
            }
        });
    }

    private function calculateShipping(int $subtotalInPaise): int
    {
        // Free shipping over ₹5,000; flat ₹250 otherwise. Adjust to real courier rates later.
        return $subtotalInPaise >= 500000 ? 0 : 25000;
    }

    private function generateOrderNumber(): string
    {
        return 'PC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
