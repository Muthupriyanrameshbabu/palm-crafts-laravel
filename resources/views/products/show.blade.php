@extends('layouts.app')

@section('title', $product->name . ' | THE PALM CRAFTS')
@section('meta_description', $product->meta_description ?? \Illuminate\Support\Str::limit($product->description, 155))

@section('content')
<section class="max-w-7xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-14">
    <div>
        <div class="aspect-square bg-brass/5 border border-ink/10 dark:border-palm-cream/10 mb-4 overflow-hidden">
            @if($product->images->first())
                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                     class="w-full h-full object-cover">
            @endif
        </div>
        @if($product->images->count() > 1)
            <div class="grid grid-cols-4 gap-3">
                @foreach($product->images->skip(1) as $img)
                    <div class="aspect-square bg-brass/5 border border-ink/10 dark:border-palm-cream/10 overflow-hidden">
                        <img src="{{ $img->url() }}" alt="{{ $img->alt_text ?? $product->name }}" class="w-full h-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div x-data="{ quantity: 1, variantId: {{ $product->variants->first()->id ?? 'null' }} }">
        <p class="eyebrow mb-3">{{ $product->category?->name ?? 'Bags' }}</p>
        <h1 class="font-display text-3xl md:text-4xl mb-3">{{ $product->name }}</h1>
        <p class="font-mono text-lg text-brass mb-6">{{ $product->price_formatted }}</p>

        <p class="text-ink/70 dark:text-palm-cream/70 leading-relaxed mb-8">{{ $product->description }}</p>

        <dl class="space-y-3 text-sm border-t border-b border-ink/10 dark:border-palm-cream/10 py-6 mb-8">
            @if($product->origin)
                <div class="flex justify-between"><dt class="text-ink/50 dark:text-palm-cream/50">Origin</dt><dd>{{ $product->origin }}</dd></div>
            @endif
            @if($product->material)
                <div class="flex justify-between"><dt class="text-ink/50 dark:text-palm-cream/50">Material</dt><dd>{{ $product->material }}</dd></div>
            @endif
            @if($product->dimensions)
                <div class="flex justify-between"><dt class="text-ink/50 dark:text-palm-cream/50">Dimensions</dt><dd>{{ $product->dimensions }}</dd></div>
            @endif
            @if($product->craft_time)
                <div class="flex justify-between"><dt class="text-ink/50 dark:text-palm-cream/50">Craft time</dt><dd>{{ $product->craft_time }}</dd></div>
            @endif
        </dl>

        @if($product->inStock())
            <form action="{{ route('cart.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @if($product->variants->isNotEmpty())
                    <div>
                        <label for="variant_id" class="block text-xs font-mono uppercase tracking-widest text-ink/50 dark:text-palm-cream/50 mb-2">
                            Option
                        </label>
                        <select name="variant_id" id="variant_id" x-model="variantId"
                                class="w-full border border-ink/20 dark:border-palm-cream/20 bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-brass">
                            @foreach($product->variants as $variant)
                                <option value="{{ $variant->id }}" @disabled($variant->stock_quantity < 1)>
                                    {{ $variant->name }} @if($variant->stock_quantity < 1) (Out of stock) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <div class="flex items-center border border-ink/20 dark:border-palm-cream/20">
                        <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="w-10 h-10 hover:bg-ink/5">-</button>
                        <input type="number" name="quantity" x-model.number="quantity" min="1" max="20"
                               class="w-14 text-center bg-transparent focus:outline-none" aria-label="Quantity">
                        <button type="button" @click="quantity = Math.min(20, quantity + 1)" class="w-10 h-10 hover:bg-ink/5">+</button>
                    </div>
                    <button type="submit" class="btn-primary flex-1 justify-center">
                        Add To Cart <span class="material-symbols-outlined text-base">shopping_bag</span>
                    </button>
                </div>
            </form>
        @else
            <p class="font-mono text-sm text-kolam-red uppercase tracking-widest">Currently out of stock</p>
        @endif
    </div>
</section>
@endsection
