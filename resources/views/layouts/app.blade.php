<!DOCTYPE html>
<html lang="en" x-data="{ dark: false }" x-bind:class="{ 'dark': dark }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'THE PALM CRAFTS by selvamani | Luxury Handcrafted Indian Palm-Leaf Artistry')</title>
    <meta name="description" content="@yield('meta_description', 'Discover premium palm-leaf weaving, handcrafted baskets, custom brass Kolam plates, and luxury artisan goods celebrating South Indian village craft traditions.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dark:bg-palmyra-brown dark:text-palm-cream transition-colors duration-500" x-data="{ cartOpen: false }">

    <header class="sticky top-0 z-40 bg-palm-cream/90 dark:bg-palmyra-brown/90 backdrop-blur border-b border-ink/10 dark:border-palm-cream/10">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-display text-xl tracking-tight">
                THE PALM CRAFTS <span class="text-brass font-body text-sm align-top">by selvamani</span>
            </a>

            <nav class="hidden md:flex items-center gap-8 font-body text-sm">
                <a href="{{ route('home') }}#hero" class="hover:text-kolam-red transition-colors">Home</a>
                <a href="{{ route('products.index') }}" class="hover:text-kolam-red transition-colors">Collections</a>
                <a href="{{ route('home') }}#craft" class="hover:text-kolam-red transition-colors">The Craft</a>
                <a href="{{ route('home') }}#kolam-studio" class="hover:text-kolam-red transition-colors">Kolam Studio</a>
                <a href="{{ route('home') }}#footer" class="hover:text-kolam-red transition-colors">Our Story</a>
            </nav>

            <div class="flex items-center gap-4">
                <button @click="dark = !dark" aria-label="Toggle dark mode" class="p-2 hover:text-brass transition-colors">
                    <span x-show="!dark" class="material-symbols-outlined text-xl">dark_mode</span>
                    <span x-show="dark" class="material-symbols-outlined text-xl">light_mode</span>
                </button>

                <a href="{{ route('cart.show') }}" class="relative p-2 hover:text-brass transition-colors" aria-label="View shopping bag">
                    <span class="material-symbols-outlined text-xl">shopping_bag</span>
                    @if(($cartCount ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 bg-kolam-red text-palm-cream text-[10px] w-4 h-4 rounded-full flex items-center justify-center">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="max-w-7xl mx-auto px-6 pt-4" role="status">
            <div class="bg-woven-olive/10 border border-woven-olive text-woven-olive px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error') || $errors->any())
        <div class="max-w-7xl mx-auto px-6 pt-4" role="alert">
            <div class="bg-kolam-red/10 border border-kolam-red text-kolam-red px-4 py-3 text-sm">
                {{ session('error') ?? $errors->first() }}
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer id="footer" class="mt-24 border-t border-ink/10 dark:border-palm-cream/10 bg-ink text-palm-cream">
        <div class="max-w-7xl mx-auto px-6 py-16 grid grid-cols-1 md:grid-cols-4 gap-10">
            <div>
                <p class="font-display text-lg mb-3">THE PALM CRAFTS <span class="text-brass font-body text-xs align-top">by selvamani</span></p>
                <p class="text-sm text-palm-cream/70 leading-relaxed">
                    Honoring historical South Indian artistry. Empowering rural weavers and preserving structural
                    basket-weaving patterns since 1982.
                </p>
            </div>
            <div>
                <p class="eyebrow mb-4">Collections</p>
                <ul class="space-y-2 text-sm text-palm-cream/70">
                    <li><a href="{{ route('products.index') }}" class="hover:text-brass">Luxe Tote Bags</a></li>
                    <li><a href="{{ route('products.index') }}" class="hover:text-brass">Chettinad Baskets</a></li>
                    <li><a href="{{ route('products.index') }}" class="hover:text-brass">Artisan Trinket Boxes</a></li>
                    <li><a href="{{ route('products.index') }}" class="hover:text-brass">Etched Brass Plates</a></li>
                </ul>
            </div>
            <div>
                <p class="eyebrow mb-4">Inquire</p>
                <ul class="space-y-2 text-sm text-palm-cream/70">
                    <li><a href="#" class="hover:text-brass">Bespoke Orders</a></li>
                    <li><a href="#" class="hover:text-brass">Artisan Collective</a></li>
                    <li><a href="#" class="hover:text-brass">Wholesale</a></li>
                    <li><a href="#" class="hover:text-brass">Journal</a></li>
                </ul>
            </div>
            <div>
                <p class="eyebrow mb-4">Join the Collective</p>
                <p class="text-sm text-palm-cream/70 mb-3">Subscribe to get studio insights, restock details, and cultural essays.</p>
                <form class="flex border-b border-palm-cream/30 focus-within:border-brass">
                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input id="newsletter-email" type="email" required placeholder="you@example.com"
                           class="bg-transparent flex-1 py-2 text-sm placeholder:text-palm-cream/40 focus:outline-none">
                    <button type="submit" aria-label="Subscribe" class="p-2 hover:text-brass">
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="border-t border-palm-cream/10">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between gap-2 text-xs text-palm-cream/50">
                <p>&copy; {{ date('Y') }} THE PALM CRAFTS by selvamani. Handcrafted under fair trade values in Tamil Nadu, India.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-brass">Privacy Policy</a>
                    <a href="#" class="hover:text-brass">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" />
    @stack('scripts')
</body>
</html>
