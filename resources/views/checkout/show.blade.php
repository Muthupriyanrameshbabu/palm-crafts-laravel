@extends('layouts.app')

@section('title', 'Checkout | THE PALM CRAFTS')

@section('content')
<section class="max-w-5xl mx-auto px-6 py-16 grid md:grid-cols-3 gap-12">
    <div class="md:col-span-2">
        <h1 class="font-display text-3xl mb-8">Checkout</h1>

        <form action="{{ route('checkout.initiate') }}" method="POST" class="space-y-8" x-data="{ useExisting: {{ $addresses->isNotEmpty() ? 'true' : 'false' }} }">
            @csrf

            <div>
                <h2 class="eyebrow mb-4">Contact</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">Email</label>
                        <input type="email" name="email" id="email" required value="{{ old('email', auth()->user()->email ?? '') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                    <div>
                        <label for="phone" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">Phone</label>
                        <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="eyebrow mb-4">Shipping Address</h2>

                @if($addresses->isNotEmpty())
                    <div class="space-y-2 mb-4">
                        @foreach($addresses as $address)
                            <label class="flex items-start gap-3 border border-ink/20 dark:border-palm-cream/20 p-4 cursor-pointer has-[:checked]:border-brass">
                                <input type="radio" name="address_id" value="{{ $address->id }}" @checked($loop->first) @click="useExisting = true" class="mt-1">
                                <span class="text-sm">
                                    {{ $address->full_name }}<br>
                                    {{ $address->line_1 }}{{ $address->line_2 ? ', '.$address->line_2 : '' }}<br>
                                    {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                                </span>
                            </label>
                        @endforeach
                        <button type="button" @click="useExisting = false" class="text-xs text-brass underline">
                            Use a different address
                        </button>
                    </div>
                @endif

                <div x-show="!useExisting" class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">Full name</label>
                        <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                    <div class="md:col-span-2">
                        <label for="line_1" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">Address line 1</label>
                        <input type="text" name="line_1" id="line_1" value="{{ old('line_1') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                    <div class="md:col-span-2">
                        <label for="line_2" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">Address line 2 (optional)</label>
                        <input type="text" name="line_2" id="line_2" value="{{ old('line_2') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                    <div>
                        <label for="city" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">City</label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                    <div>
                        <label for="state" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">State</label>
                        <input type="text" name="state" id="state" value="{{ old('state') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                    <div>
                        <label for="postal_code" class="block text-xs text-ink/50 dark:text-palm-cream/50 mb-1">Postal code</label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}"
                               class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center">Continue to Payment</button>
        </form>
    </div>

    <div>
        <h2 class="eyebrow mb-4">Order Summary</h2>
        <div class="space-y-4 border-t border-ink/10 dark:border-palm-cream/10 pt-4">
            @foreach($cart->items as $item)
                <div class="flex justify-between text-sm">
                    <span>{{ $item->product->name }} &times; {{ $item->quantity }}</span>
                    <span class="font-mono">₹{{ number_format($item->lineTotalInPaise() / 100, 2) }}</span>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-sm font-medium border-t border-ink/10 dark:border-palm-cream/10 mt-4 pt-4">
            <span>Subtotal</span>
            <span class="font-mono">₹{{ number_format($cart->subtotalInPaise() / 100, 2) }}</span>
        </div>
        <p class="text-xs text-ink/40 dark:text-palm-cream/40 mt-2">
            Free shipping over ₹5,000, otherwise a flat ₹250 applies. Final total shown on the next step.
        </p>
    </div>
</section>
@endsection
