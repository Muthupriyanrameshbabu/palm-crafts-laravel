<?php

namespace App\Services;

use App\Models\Order;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret'),
        );
    }

    /**
     * Creates a Razorpay order tied to our internal Order record.
     * Must be called BEFORE showing the checkout widget — Razorpay requires
     * an order_id to be generated server-side first, so the amount can't be
     * tampered with client-side.
     */
    public function createOrder(Order $order): string
    {
        $razorpayOrder = $this->api->order->create([
            'receipt' => $order->order_number,
            'amount' => $order->total_in_paise, // Razorpay expects the smallest currency unit
            'currency' => 'INR',
            'notes' => [
                'internal_order_id' => $order->id,
            ],
        ]);

        $order->update(['razorpay_order_id' => $razorpayOrder->id]);

        return $razorpayOrder->id;
    }

    /**
     * Verifies the payment signature returned by Razorpay's checkout callback.
     * This MUST pass before we ever mark an order as paid — it's the only proof
     * the payment response wasn't forged by a malicious client.
     */
    public function verifyPaymentSignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ]);

            return true;
        } catch (SignatureVerificationError $e) {
            report($e);

            return false;
        }
    }

    /**
     * Verifies the webhook signature (different secret from the checkout signature).
     * Used by the webhook controller to confirm the request truly came from Razorpay.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $this->api->utility->verifyWebhookSignature(
                $payload,
                $signature,
                config('services.razorpay.webhook_secret'),
            );

            return true;
        } catch (SignatureVerificationError $e) {
            report($e);

            return false;
        }
    }
}
