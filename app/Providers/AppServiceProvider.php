<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Cart badge count needs to appear in the header on every page, so it's
        // shared globally here rather than fetched redundantly in each controller.
        View::composer('layouts.app', function ($view) {
            try {
                $cart = app(CartService::class)->current();
                $view->with('cartCount', $cart->totalQuantity());
            } catch (\Throwable $e) {
                $view->with('cartCount', 0);
            }
        });
    }
}
