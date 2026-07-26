<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_within_stock_succeeds(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5, 'price_in_paise' => 100000]);

        $cart = app(CartService::class)->add($product, 3);

        $this->assertEquals(3, $cart->items->first()->quantity);
    }

    public function test_adding_more_than_available_stock_throws(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5, 'price_in_paise' => 100000]);

        $this->expectException(ValidationException::class);

        app(CartService::class)->add($product, 6);
    }

    public function test_adding_twice_correctly_sums_against_stock_limit(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5, 'price_in_paise' => 100000]);
        $service = app(CartService::class);

        $service->add($product, 3);

        // Second add pushes total requested to 6, which exceeds the 5 in stock —
        // this is the exact bug class that would let someone oversell by adding
        // the same item to their cart in multiple small increments.
        $this->expectException(ValidationException::class);
        $service->add($product, 3);
    }

    public function test_price_is_snapshotted_at_add_time(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10, 'price_in_paise' => 50000]);
        $cart = app(CartService::class)->add($product, 1);

        $product->update(['price_in_paise' => 99999]);

        $cart->refresh();
        $this->assertEquals(50000, $cart->items->first()->unit_price_in_paise);
    }
}
