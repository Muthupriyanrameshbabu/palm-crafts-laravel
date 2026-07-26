<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/collections', [ProductController::class, 'index'])->name('products.index');
Route::get('/collections/{category:slug}', [ProductController::class, 'index'])->name('products.byCategory');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'show'])->name('show');
    Route::post('/', [CheckoutController::class, 'initiate'])->name('initiate');
    Route::get('/{order}/pay', [CheckoutController::class, 'pay'])->name('pay');
    Route::post('/{order}/callback', [CheckoutController::class, 'callback'])->name('callback');
    Route::get('/{order}/success', [CheckoutController::class, 'success'])->name('success');
});

// Server-to-server webhook — no CSRF, no session/auth middleware. Signature-verified inside the controller.
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');

require __DIR__.'/auth.php';
