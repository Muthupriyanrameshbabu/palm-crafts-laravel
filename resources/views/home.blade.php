@extends('layouts.app')

@section('content')

{{-- HERO --}}
<section id="hero" class="relative overflow-hidden">
    <div class="weave-texture absolute inset-0 opacity-60"></div>
    <div class="relative max-w-7xl mx-auto px-6 pt-16 pb-24 md:pt-24 md:pb-32 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <p class="eyebrow mb-4">South Indian Artisan Craft</p>
            <h1 class="font-display text-5xl md:text-6xl leading-[1.05] mb-6">
                The Grace of<br><span class="text-kolam-red">Palm Weaving</span>
            </h1>
            <p class="text-ink/70 dark:text-palm-cream/70 text-lg max-w-md mb-8 leading-relaxed">
                Honoring Tamil Nadu's ancient weaving traditions. Handcrafted palm-leaf bags, baskets,
                and decor that merge organic village craftsmanship with international luxury design.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('products.index') }}" class="btn-primary">Explore Collection</a>
                <a href="#craft" class="btn-secondary">Our Heritage</a>
            </div>
        </div>
        <div class="relative">
            <div class="aspect-[4/5] bg-brass/10 border border-brass/30 flex items-center justify-center overflow-hidden">
                @if($featuredImage ?? false)
                    <img src="{{ $featuredImage }}" alt="Luxury Handwoven Palm-Leaf Tote Bag" class="w-full h-full object-cover">
                @else
                    <span class="font-mono text-xs text-brass uppercase tracking-widest">Featured product image</span>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- COLLECTIONS PREVIEW --}}
<section id="collections" class="max-w-7xl mx-auto px-6 py-20">
    <div class="flex items-end justify-between mb-10 flex-wrap gap-4">
        <div>
            <p class="eyebrow mb-3">Shop The Craft</p>
            <h2 class="font-display text-3xl md:text-4xl">The Heritage Collection</h2>
            <p class="text-ink/60 dark:text-palm-cream/60 mt-2 max-w-lg">
                Earthy textures meets luxury function. Designed for the global connoisseur.
            </p>
        </div>
        <a href="{{ route('products.index') }}" class="btn-secondary">All Objects</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @forelse(($featuredProducts ?? []) as $product)
            <a href="{{ route('products.show', $product) }}" class="group">
                <div class="aspect-square bg-brass/5 border border-ink/10 dark:border-palm-cream/10 mb-3 overflow-hidden">
                    @if($product->images->first())
                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @endif
                </div>
                <p class="font-body text-sm">{{ $product->name }}</p>
                <p class="font-mono text-xs text-brass">{{ $product->price_formatted }}</p>
            </a>
        @empty
            <p class="col-span-4 text-sm text-ink/50 dark:text-palm-cream/50">
                No products published yet — add some in the admin panel.
            </p>
        @endforelse
    </div>
</section>

{{-- CRAFT PROCESS — numbering here is legitimate: it's a real, ordered making process. --}}
<section id="craft" class="bg-ink text-palm-cream py-24">
    <div class="max-w-7xl mx-auto px-6">
        <p class="eyebrow mb-3">Made By Hand</p>
        <h2 class="font-display text-3xl md:text-4xl max-w-xl mb-4">The Artistry Behind Palm-Leaf Weaving</h2>
        <p class="text-palm-cream/60 max-w-xl mb-16 leading-relaxed">
            Deep in Tamil Nadu's rural villages, female artisans preserve the complex, geometric discipline
            of palm frond manipulation. Every product takes days of structured weaving.
        </p>

        <div class="grid md:grid-cols-4 gap-10">
            @php
                $steps = [
                    ['01', 'Frond Harvesting', 'Selecting wild, mature leaves from the Palmyra Palm. Only sustainable, non-disruptive branches are harvested.'],
                    ['02', 'Boiling & Sundrying', 'Fibers are split, boiled with salt, and sundried for 4 days to build a natural gold luster and flexible strength.'],
                    ['03', 'Organic Dyeing', 'Strands are steeped in organic pigment extracts sourced from local roots, bark, and flowers for rich, earth-toned hues.'],
                    ['04', 'Geometric Hand-Weave', 'Interlaced using traditional basket-weave and diagonal-rib structures, creating strong, mathematical patterns.'],
                ];
            @endphp
            @foreach($steps as [$num, $title, $desc])
                <div>
                    <p class="font-mono text-brass text-sm mb-3">{{ $num }}</p>
                    <h3 class="font-display text-xl mb-2">{{ $title }}</h3>
                    <p class="text-palm-cream/60 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- KOLAM STUDIO — signature interactive element --}}
<section id="kolam-studio" class="max-w-7xl mx-auto px-6 py-24">
    <div class="text-center max-w-xl mx-auto mb-12">
        <p class="eyebrow mb-3">Interactive Ritual</p>
        <h2 class="font-display text-3xl md:text-4xl mb-4">South Indian Kolam Studio</h2>
        <p class="text-ink/60 dark:text-palm-cream/60 leading-relaxed">
            A morning threshold ritual. In South Indian homes, symmetrical curves are drawn around grids of
            dots to bring prosperity and order. Click on dots in the workspace to sketch a custom geometric
            pattern, or project traditional layouts using the filters.
        </p>
    </div>

    <div x-data="kolamStudio()" x-init="init()" class="border border-ink/10 dark:border-palm-cream/10 p-6 md:p-10">
        <div class="flex flex-wrap gap-3 justify-center mb-8">
            <button @click="clearCanvas()" class="btn-secondary text-xs py-2 px-4">Clear Canvas</button>
            <button @click="loadPreset('welcome')" class="btn-secondary text-xs py-2 px-4">Traditional Welcome</button>
            <button @click="loadPreset('lotus')" class="btn-secondary text-xs py-2 px-4">Lotus Blossom</button>
        </div>

        <div class="flex justify-center">
            <svg :viewBox="`0 0 ${size} ${size}`" width="360" height="360" class="max-w-full">
                {{-- connecting lines --}}
                <template x-for="(line, i) in lines" :key="i">
                    <line :x1="line.x1" :y1="line.y1" :x2="line.x2" :y2="line.y2"
                          stroke="currentColor" class="text-kolam-red" stroke-width="3" stroke-linecap="round" />
                </template>
                {{-- dot grid --}}
                <template x-for="(dot, i) in dots" :key="i">
                    <circle :cx="dot.x" :cy="dot.y" r="5"
                            @click="selectDot(i)"
                            :class="selected === i ? 'fill-kolam-red' : 'fill-current text-brass'"
                            class="cursor-pointer hover:fill-kolam-red transition-colors" />
                </template>
            </svg>
        </div>
        <p class="text-center text-xs text-ink/40 dark:text-palm-cream/40 mt-4 font-mono">
            Click two dots in sequence to draw a connecting line.
        </p>
    </div>
</section>

@push('scripts')
<script>
    function kolamStudio() {
        const gridSize = 6;
        const spacing = 60;
        const size = spacing * (gridSize + 1);

        function buildGrid() {
            const dots = [];
            for (let row = 1; row <= gridSize; row++) {
                for (let col = 1; col <= gridSize; col++) {
                    dots.push({ x: col * spacing, y: row * spacing });
                }
            }
            return dots;
        }

        return {
            size,
            dots: buildGrid(),
            lines: [],
            selected: null,
            init() {},
            selectDot(i) {
                if (this.selected === null) {
                    this.selected = i;
                    return;
                }
                if (this.selected === i) {
                    this.selected = null;
                    return;
                }
                const a = this.dots[this.selected];
                const b = this.dots[i];
                this.lines.push({ x1: a.x, y1: a.y, x2: b.x, y2: b.y });
                this.selected = null;
            },
            clearCanvas() {
                this.lines = [];
                this.selected = null;
            },
            loadPreset(name) {
                this.clearCanvas();
                const g = spacing;
                const presets = {
                    welcome: [
                        [1,1,2,2],[2,2,1,3],[1,3,2,4],[2,4,1,5],[1,5,2,6],
                        [6,1,5,2],[5,2,6,3],[6,3,5,4],[5,4,6,5],[6,5,5,6],
                        [1,1,6,1],[1,6,6,6],
                    ],
                    lotus: [
                        [3,1,4,2],[4,2,3,3],[3,3,4,4],
                        [1,3,2,4],[2,4,1,5],
                        [6,3,5,4],[5,4,6,5],
                        [3,6,4,5],[4,5,3,4],
                    ],
                };
                (presets[name] || []).forEach(([c1, r1, c2, r2]) => {
                    this.lines.push({
                        x1: c1 * g, y1: r1 * g,
                        x2: c2 * g, y2: r2 * g,
                    });
                });
            },
        };
    }
</script>
@endpush

@endsection
