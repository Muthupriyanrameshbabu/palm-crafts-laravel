<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles server-to-server payment confirmation from Razorpay.
 *
 * This is the SOURCE OF TRUTH for marking orders paid — not the browser
 * redirect callback. Browsers can lose connection after payment succeeds
 * (closed tab, network drop) before hitting our callback route; the webhook
 * guarantees we still record the payment. CheckoutController::confirmPayment()
 * is idempotent, so it's safe for both paths to call it.
 *
 * Route for this must be excluded from CSRF verification (see bootstrap/app.php)
 * since Razorpay's servers can't supply a Laravel CSRF token.
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpayService,
        private readonly OrderService $orderService,
    ) {}

    public function handle(Request $request): Response
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if (! $this->razorpayService->verifyWebhookSignature($payload, $signature)) {
            report(new \Exception('Razorpay webhook signature verification failed.'));

            return response('Invalid signature', 400);
        }

        $event = $request->input('event');

        if ($event === 'payment.captured') {
            $payment = $request->input('payload.payment.entity');
            $razorpayOrderId = $payment['order_id'] ?? null;

            $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();

            if ($order) {
                $this->orderService->confirmPayment(
                    $order,
                    $payment['id'],
                    $signature, // webhook signature stored for audit; distinct from checkout signature
                );
            } else {
                report(new \Exception("Razorpay webhook: no matching order for razorpay_order_id {$razorpayOrderId}"));
            }
        }

        // Always return 200 for events we don't act on, so Razorpay doesn't keep retrying.
        return response('OK', 200);
    }
}
