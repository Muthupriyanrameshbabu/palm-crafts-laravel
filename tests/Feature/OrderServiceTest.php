<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmationMail;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private function buildPendingOrder(int $stock = 10, int $quantity = 2): Order
    {
        $product = Product::factory()->create(['stock_quantity' => $stock, 'price_in_paise' => 100000]);
        $cart = app(CartService::class)->add($product, $quantity);
        $address = Address::factory()->create();

        return app(OrderService::class)->createFromCart($cart, $address, 'buyer@example.com', '9999999999');
    }

    public function test_confirming_payment_deducts_stock_exactly_once(): void
    {
        Mail::fake();

        $order = $this->buildPendingOrder(stock: 10, quantity: 2);
        $product = $order->items->first()->product;

        app(OrderService::class)->confirmPayment($order, 'pay_test123', 'sig_test123');

        $this->assertEquals(8, $product->fresh()->stock_quantity);
        $this->assertEquals('paid', $order->fresh()->status);
    }

    /**
     * This is the critical regression test: both the browser redirect callback
     * and the Razorpay webhook can call confirmPayment() for the same order.
     * If this isn't idempotent, stock gets decremented twice for one payment —
     * a real inventory/revenue bug, not a theoretical one.
     */
    public function test_confirming_payment_twice_only_deducts_stock_once(): void
    {
        Mail::fake();

        $order = $this->buildPendingOrder(stock: 10, quantity: 2);
        $product = $order->items->first()->product;
        $service = app(OrderService::class);

        $service->confirmPayment($order, 'pay_test123', 'sig_test123');
        $service->confirmPayment($order, 'pay_test123', 'sig_test123'); // simulates webhook + redirect race

        $this->assertEquals(8, $product->fresh()->stock_quantity, 'Stock was deducted more than once for a single payment.');
    }

    public function test_confirming_payment_sends_confirmation_email_only_once(): void
    {
        Mail::fake();

        $order = $this->buildPendingOrder();
        $service = app(OrderService::class);

        $service->confirmPayment($order, 'pay_test123', 'sig_test123');
        $service->confirmPayment($order, 'pay_test123', 'sig_test123');

        Mail::assertSent(OrderConfirmationMail::class, 1);
    }

    public function test_checkout_rejects_cart_exceeding_current_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 3, 'price_in_paise' => 100000]);
        $cart = app(CartService::class)->add($product, 3);
        $address = Address::factory()->create();

        // Stock sells out elsewhere between add-to-cart and checkout.
        $product->update(['stock_quantity' => 1]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(OrderService::class)->createFromCart($cart, $address, 'buyer@example.com', '9999999999');
    }
}
