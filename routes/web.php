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

Route::prefix('cart')->name('cart.')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/', [CartController::class, 'store'])->name('store');
    Route::patch('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'destroy'])->name('destroy');
});

Route::prefix('checkout')->name('checkout.')->middleware('throttle:20,1')->group(function () {
    Route::get('/', [CheckoutController::class, 'show'])->name('show');
    Route::post('/', [CheckoutController::class, 'initiate'])->name('initiate');
    Route::get('/{order}/pay', [CheckoutController::class, 'pay'])->name('pay');
    Route::post('/{order}/callback', [CheckoutController::class, 'callback'])->name('callback');
    Route::get('/{order}/success', [CheckoutController::class, 'success'])->name('success');
});

// Webhook has its own generous limit — Razorpay may legitimately retry rapidly, but this
// still caps runaway/malicious traffic. Signature verification is the real gatekeeper.
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('webhooks.razorpay');

require __DIR__.'/auth.php';
