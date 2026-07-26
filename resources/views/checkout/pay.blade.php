@extends('layouts.app')

@section('title', 'Payment | THE PALM CRAFTS')

@section('content')
<section class="max-w-md mx-auto px-6 py-24 text-center">
    <p class="eyebrow mb-3">Order {{ $order->order_number }}</p>
    <h1 class="font-display text-3xl mb-4">Complete Your Payment</h1>
    <p class="text-ink/60 dark:text-palm-cream/60 mb-8">
        Total due: <span class="font-mono text-brass">₹{{ number_format($order->total_in_paise / 100, 2) }}</span>
    </p>

    <button id="pay-button" class="btn-primary w-full justify-center">Pay Now</button>
    <p id="pay-error" class="text-kolam-red text-sm mt-4 hidden" role="alert"></p>
</section>

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('pay-button').addEventListener('click', function () {
        const options = {
            key: @json($razorpayKey),
            order_id: @json($order->razorpay_order_id),
            amount: @json($order->total_in_paise),
            currency: 'INR',
            name: 'THE PALM CRAFTS by selvamani',
            description: 'Order {{ $order->order_number }}',
            prefill: {
                email: @json($order->email),
                contact: @json($order->phone),
            },
            theme: { color: '#A8432D' },
            handler: function (response) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('checkout.callback', $order));

                const fields = {
                    _token: @json(csrf_token()),
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                };
                for (const key in fields) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            },
            modal: {
                ondismiss: function () {
                    document.getElementById('pay-error').textContent = 'Payment was cancelled. You can try again below.';
                    document.getElementById('pay-error').classList.remove('hidden');
                },
            },
        };

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response) {
            document.getElementById('pay-error').textContent = 'Payment failed: ' + response.error.description;
            document.getElementById('pay-error').classList.remove('hidden');
        });
        rzp.open();
    });
</script>
@endpush
@endsection
