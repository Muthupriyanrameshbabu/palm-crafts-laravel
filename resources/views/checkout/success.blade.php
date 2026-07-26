@extends('layouts.app')

@section('title', 'Order Confirmed | THE PALM CRAFTS')

@section('content')
<section class="max-w-2xl mx-auto px-6 py-24 text-center">
    <span class="material-symbols-outlined text-5xl text-woven-olive mb-4 inline-block">check_circle</span>
    <p class="eyebrow mb-3">Order {{ $order->order_number }}</p>
    <h1 class="font-display text-3xl mb-4">Thank You For Your Order</h1>
    <p class="text-ink/60 dark:text-palm-cream/60 mb-10">
        A confirmation has been sent to {{ $order->email }}. Your palm-leaf pieces are now being
        prepared for their journey from the village workshop to your door.
    </p>

    <div class="text-left border-t border-b border-ink/10 dark:border-palm-cream/10 divide-y divide-ink/10 dark:divide-palm-cream/10">
        @foreach($order->items as $item)
            <div class="py-4 flex justify-between text-sm">
                <span>{{ $item->product_name }}{{ $item->variant_name ? ' — '.$item->variant_name : '' }} &times; {{ $item->quantity }}</span>
                <span class="font-mono">₹{{ number_format($item->line_total_in_paise / 100, 2) }}</span>
            </div>
        @endforeach
    </div>

    <div class="flex justify-between text-sm font-medium mt-4">
        <span>Total Paid</span>
        <span class="font-mono">₹{{ number_format($order->total_in_paise / 100, 2) }}</span>
    </div>

    <a href="{{ route('products.index') }}" class="btn-primary mt-10 inline-flex">Continue Browsing</a>
</section>
@endsection
