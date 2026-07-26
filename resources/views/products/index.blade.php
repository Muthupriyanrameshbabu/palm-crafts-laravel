@extends('layouts.app')

@section('title', ($category->name ?? 'Collections') . ' | THE PALM CRAFTS')

@section('content')
<section class="max-w-7xl mx-auto px-6 py-16">
    <p class="eyebrow mb-3">Shop The Craft</p>
    <h1 class="font-display text-4xl mb-8">{{ $category->name ?? 'All Objects' }}</h1>

    <div class="flex flex-wrap gap-3 mb-10">
        <a href="{{ route('products.index') }}"
           class="{{ !$category ? 'btn-primary' : 'btn-secondary' }} text-xs py-2 px-4">All</a>
        @foreach($categories as $cat)
            <a href="{{ route('products.byCategory', $cat) }}"
               class="{{ ($category?->id === $cat->id) ? 'btn-primary' : 'btn-secondary' }} text-xs py-2 px-4">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
        @forelse($products as $product)
            <a href="{{ route('products.show', $product) }}" class="group">
                <div class="aspect-square bg-brass/5 border border-ink/10 dark:border-palm-cream/10 mb-3 overflow-hidden relative">
                    @if($product->images->first())
                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @endif
                    @unless($product->inStock())
                        <span class="absolute top-3 left-3 bg-ink text-palm-cream text-[10px] font-mono uppercase tracking-widest px-2 py-1">
                            Out of stock
                        </span>
                    @endunless
                </div>
                <p class="font-body text-sm">{{ $product->name }}</p>
                <p class="font-mono text-xs text-brass">{{ $product->price_formatted }}</p>
            </a>
        @empty
            <p class="col-span-3 text-sm text-ink/50 dark:text-palm-cream/50">
                No pieces in this collection yet. Check back soon.
            </p>
        @endforelse
    </div>

    <div class="mt-12">{{ $products->links() }}</div>
</section>
@endsection
