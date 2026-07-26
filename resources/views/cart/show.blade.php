@extends('layouts.app')

@section('title', 'Your Bag | THE PALM CRAFTS')

@section('content')
<section class="max-w-5xl mx-auto px-6 py-16">
    <h1 class="font-display text-3xl mb-10">Your Bag</h1>

    @if($cart->items->isEmpty())
        <p class="text-ink/60 dark:text-palm-cream/60 mb-8">Your bag is empty.</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Explore the Collection</a>
    @else
        <div class="divide-y divide-ink/10 dark:divide-palm-cream/10 border-t border-b border-ink/10 dark:border-palm-cream/10">
            @foreach($cart->items as $item)
                <div class="py-6 flex gap-6 items-center">
                    <div class="w-24 h-24 bg-brass/5 border border-ink/10 dark:border-palm-cream/10 shrink-0 overflow-hidden">
                        @if($item->product->images->first())
                            <img src="{{ $item->product->images->first()->url() }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                        @endif
                    </div>

                    <div class="flex-1">
                        <p class="font-body text-sm mb-1">{{ $item->product->name }}</p>
                        @if($item->variant)
                            <p class="text-xs text-ink/50 dark:text-palm-cream/50 mb-1">{{ $item->variant->name }}</p>
                        @endif
                        <p class="font-mono text-xs text-brass">₹{{ number_format($item->unit_price_in_paise / 100, 2) }}</p>
                    </div>

                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center border border-ink/20 dark:border-palm-cream/20">
                        @csrf @method('PATCH')
                        <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}" class="w-8 h-8 hover:bg-ink/5" aria-label="Decrease quantity">-</button>
                        <span class="w-8 text-center text-sm">{{ $item->quantity }}</span>
                        <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="w-8 h-8 hover:bg-ink/5" aria-label="Increase quantity">+</button>
                    </form>

                    <p class="font-mono text-sm w-24 text-right">₹{{ number_format($item->lineTotalInPaise() / 100, 2) }}</p>

                    <form action="{{ route('cart.destroy', $item->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-ink/40 hover:text-kolam-red transition-colors" aria-label="Remove {{ $item->product->name }} from bag">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end mt-8">
            <div class="w-full max-w-xs space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-ink/60 dark:text-palm-cream/60">Subtotal</span>
                    <span class="font-mono">₹{{ number_format($cart->subtotalInPaise() / 100, 2) }}</span>
                </div>
                <p class="text-xs text-ink/40 dark:text-palm-cream/40">Shipping and taxes calculated at checkout.</p>
                <a href="{{ route('checkout.show') }}" class="btn-primary w-full justify-center mt-4">Proceed to Checkout</a>
            </div>
        </div>
    @endif
</section>
@endsection
