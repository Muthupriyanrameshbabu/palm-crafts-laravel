<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly RazorpayService $razorpayService,
    ) {}

    public function show()
    {
        $cart = $this->cartService->current()->load('items.product', 'items.variant');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show')->with('error', 'Your bag is empty.');
        }

        $addresses = Auth::check() ? Auth::user()->addresses()->orderByDesc('is_default')->get() : collect();

        return view('checkout.show', compact('cart', 'addresses'));
    }

    /**
     * Step 1: create the pending Order + Razorpay order, then hand off to the
     * Razorpay Checkout.js widget on the frontend. Payment is NOT confirmed here.
     */
    public function initiate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_id' => ['nullable', 'exists:addresses,id'],
            'full_name' => ['required_without:address_id', 'string', 'max:255'],
            'line_1' => ['required_without:address_id', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required_without:address_id', 'string', 'max:120'],
            'state' => ['required_without:address_id', 'string', 'max:120'],
            'postal_code' => ['required_without:address_id', 'string', 'max:12'],
        ]);

        $cart = $this->cartService->current();

        $address = isset($validated['address_id'])
            ? Address::where('id', $validated['address_id'])->where('user_id', Auth::id())->firstOrFail()
            : Address::create([
                'user_id' => Auth::id(), // null for guest checkout
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'line_1' => $validated['line_1'],
                'line_2' => $validated['line_2'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
            ]);

        $order = $this->orderService->createFromCart($cart, $address, $validated['email'], $validated['phone']);
        $razorpayOrderId = $this->razorpayService->createOrder($order);

        // Lets a guest (no account) revisit their own pay/success pages without
        // exposing other people's orders — scoped to this browser session only.
        session(['checkout_email' => $order->email]);

        return redirect()->route('checkout.pay', $order)->with('razorpay_order_id', $razorpayOrderId);
    }

    public function pay(Order $order)
    {
        abort_unless(
            $order->user_id === Auth::id() || $order->email === session('checkout_email'),
            403
        );

        return view('checkout.pay', [
            'order' => $order,
            'razorpayKey' => config('services.razorpay.key'),
        ]);
    }

    /**
     * Frontend callback after Razorpay Checkout.js completes. This is a UX
     * convenience redirect ONLY — actual payment confirmation and stock
     * deduction happens via the signature-verified webhook, never here alone,
     * since this endpoint's inputs come from the client and could be spoofed.
     */
    public function callback(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $valid = $this->razorpayService->verifyPaymentSignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature'],
        );

        if (! $valid) {
            return redirect()->route('checkout.pay', $order)
                ->with('error', 'Payment verification failed. Please try again or contact support.');
        }

        $this->orderService->confirmPayment($order, $validated['razorpay_payment_id'], $validated['razorpay_signature']);

        return redirect()->route('checkout.success', $order);
    }

    public function success(Order $order)
    {
        abort_unless($order->status === 'paid', 404);

        return view('checkout.success', compact('order'));
    }
}
