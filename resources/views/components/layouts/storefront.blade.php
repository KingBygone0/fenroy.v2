<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoTitle       = $title       ?? 'Fenroy — Fresh Groceries Delivered in Ghana';
        $seoDescription = $description ?? 'Shop fresh fruits, vegetables, beverages, dairy, pantry staples and more on Fenroy — Ghana\'s online supermarket. Order now and get fast delivery to your door.';
        $seoImage       = $ogImage     ?? asset('images/fenroy-og.png');
        $seoUrl         = url()->current();
        $seoType        = $ogType      ?? 'website';
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $seoUrl }}">

    {{-- Open Graph --}}
    <meta property="og:site_name"   content="Fenroy">
    <meta property="og:type"        content="{{ $seoType }}">
    <meta property="og:title"       content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image"       content="{{ $seoImage }}">
    <meta property="og:url"         content="{{ $seoUrl }}">
    <meta property="og:locale"      content="en_GH">

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image"       content="{{ $seoImage }}">

    {{-- Structured Data --}}
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'GroceryStore',
        'name'     => 'Fenroy',
        'url'      => 'https://fenroy.shop',
        'logo'     => asset('images/fenroy-logo.png'),
        'image'    => $seoImage,
        'description' => 'Ghana\'s online supermarket — fresh groceries, beverages, dairy, pantry and household essentials delivered fast.',
        'telephone'   => '+233245176310',
        'email'       => 'hello@fenroy.shop',
        'areaServed'  => 'Ghana',
        'currenciesAccepted' => 'GHS',
        'paymentAccepted'    => 'Credit Card, Mobile Money',
        'sameAs' => [],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#16a34a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Fenroy">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @php $ga4Id = \App\Models\Setting::get('ga4_measurement_id', ''); @endphp
    @if($ga4Id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $ga4Id }}');</script>
    @endif
</head>
<body class="bg-brand-bg font-sans text-brand-text antialiased">

    {{-- ─── Announcement Banner ──────────────────────────────── --}}
    @php
        $bannerEnabled = \App\Models\Setting::get('banner_enabled', '0');
        $bannerMessage = \App\Models\Setting::get('banner_message', '');
    @endphp
    @if($bannerEnabled === '1' && $bannerMessage)
    <div class="w-full bg-brand-red text-white text-center text-sm font-medium py-2 px-4">
        {{ $bannerMessage }}
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         DESKTOP HEADER  (hidden on mobile)
    ═══════════════════════════════════════════════════════════ --}}
    <header class="hidden md:block sticky top-0 z-40 bg-white border-b border-brand-border-light">
        <div class="max-w-[1280px] mx-auto px-14 h-[72px] flex items-center gap-6 min-w-0">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="shrink-0">
                <img src="{{ asset('images/fenroy-logo.png') }}" alt="Fenroy" class="h-8 w-auto">
            </a>

            {{-- Nav links — shrink-0 prevents wrapping --}}
            <nav class="shrink-0 flex items-center gap-5 text-sm font-medium text-[#444]">
                <a href="{{ route('home') }}"
                   class="whitespace-nowrap hover:text-brand-red transition-colors duration-150 {{ request()->routeIs('home') ? 'font-bold text-brand-text' : '' }}">
                    Home
                </a>
                <a href="{{ route('category.index') }}"
                   class="whitespace-nowrap hover:text-brand-red transition-colors duration-150 {{ request()->routeIs('category.*') ? 'font-bold text-brand-text' : '' }}">
                    Categories
                </a>
                <a href="{{ route('deals') }}"
                   class="whitespace-nowrap hover:text-brand-red transition-colors duration-150 {{ request()->routeIs('deals') ? 'font-bold text-brand-text' : '' }}">
                    Deals
                </a>
                <a href="{{ route('new-arrivals') }}"
                   class="whitespace-nowrap hover:text-brand-red transition-colors duration-150 {{ request()->routeIs('new-arrivals') ? 'font-bold text-brand-text' : '' }}">
                    New Arrivals
                </a>
                <a href="{{ route('best-sellers') }}"
                   class="whitespace-nowrap hover:text-brand-red transition-colors duration-150 {{ request()->routeIs('best-sellers') ? 'font-bold text-brand-text' : '' }}">
                    Best Sellers
                </a>
            </nav>

            {{-- Right controls --}}
            <div class="ml-auto shrink-0 flex items-center gap-3">
                {{-- Search pill with autocomplete --}}
                <div x-data="searchAutocomplete()" class="relative" @click.outside="close()">
                    <form action="{{ route('search') }}" method="GET" @submit="close()">
                        <input
                            type="search"
                            name="q"
                            x-model="query"
                            @input.debounce.300ms="fetch()"
                            @keydown.escape="close()"
                            @keydown.arrow-down.prevent="moveDown()"
                            @keydown.arrow-up.prevent="moveUp()"
                            @keydown.enter.prevent="selectActive()"
                            @focus="query.length >= 2 && results.length && (open = true)"
                            placeholder="Search products…"
                            autocomplete="off"
                            class="w-[200px] xl:w-[260px] h-[42px] pl-10 pr-4 rounded-full bg-[#F5F5F5] text-sm text-brand-text placeholder-brand-muted border-0 focus:outline-none focus:ring-2 focus:ring-brand-light-red focus:bg-white transition"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-brand-muted pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </form>

                    {{-- Dropdown --}}
                    <div x-show="open && results.length"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute top-full left-0 mt-1.5 w-72 bg-white rounded-2xl shadow-lg border border-brand-border-light overflow-hidden z-50"
                         style="display:none">
                        <template x-for="(r, i) in results" :key="r.slug">
                            <a :href="'/products/' + r.slug"
                               @mouseenter="active = i"
                               class="flex items-center gap-3 px-4 py-2.5 hover:bg-[#fafafa] transition-colors cursor-pointer"
                               :class="active === i ? 'bg-brand-light-red/40' : ''">
                                <img :src="r.image" :alt="r.name" class="w-9 h-9 rounded-lg object-cover bg-gray-100 shrink-0" onerror="this.src='/images/favicon.png'">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-brand-text truncate" x-text="r.name"></div>
                                    <div class="text-xs text-brand-muted" x-text="'GHS ' + r.price.toFixed(2)"></div>
                                </div>
                            </a>
                        </template>
                        <a :href="'{{ route('search') }}?q=' + encodeURIComponent(query)"
                           class="flex items-center gap-2 px-4 py-2.5 border-t border-brand-border-light text-sm text-brand-red font-semibold hover:bg-brand-light-red/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            See all results for &ldquo;<span x-text="query"></span>&rdquo;
                        </a>
                    </div>
                </div>

                {{-- Account (authenticated dropdown / guest link) --}}
                @auth
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            class="flex items-center gap-2 h-[42px] px-3 rounded-full bg-[#F5F5F5] hover:bg-brand-light-red transition-colors text-sm font-medium text-brand-text">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="hidden lg:inline max-w-[100px] truncate">{{ auth()->user()->name }}</span>
                        <svg class="w-3 h-3 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" x-transition
                         class="absolute right-0 top-full mt-2 w-44 bg-white rounded-xl shadow-lg border border-brand-border-light py-1 z-50">
                        <a href="{{ route('account.profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-brand-text hover:bg-[#F5F5F5]">
                            <svg class="w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            My Account
                        </a>
                        <a href="{{ route('account.orders') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-brand-text hover:bg-[#F5F5F5]">
                            <svg class="w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            My Orders
                        </a>
                        <div class="my-1 border-t border-brand-border-light"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="flex items-center gap-2 h-[42px] px-3 rounded-full bg-[#F5F5F5] hover:bg-brand-light-red transition-colors text-sm font-medium text-brand-text" aria-label="Sign in">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="hidden lg:inline">Sign in</span>
                </a>
                @endauth

                {{-- Cart pill — fixed width prevents expansion as total grows --}}
                <a href="{{ route('cart') }}"
                   class="flex items-center gap-2 h-[42px] px-4 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors duration-150 cursor-pointer shrink-0 lg:w-[172px]"
                   aria-label="Shopping cart">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span data-cart-count class="tabular-nums shrink-0">{{ session('cart_count', 0) }}</span>
                    <span class="hidden lg:inline shrink-0">·</span>
                    <span class="hidden lg:inline tabular-nums truncate" data-cart-total>GH₵ {{ number_format(session('cart_total', 0), 2) }}</span>
                </a>
            </div>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════════════════════
         MOBILE HEADER  (visible below md)
    ═══════════════════════════════════════════════════════════ --}}
    <header class="md:hidden sticky top-0 z-40 bg-white border-b border-brand-border-light">
        <div class="px-4 h-14 flex items-center gap-3">
            <a href="{{ route('home') }}" class="shrink-0">
                <img src="{{ asset('images/fenroy-logo.png') }}" alt="Fenroy" class="h-9 w-auto">
            </a>
            <div class="ml-auto flex items-center gap-2">
                {{-- Notifications --}}
                <button class="w-11 h-11 flex items-center justify-center rounded-full bg-[#F5F5F5]" aria-label="Notifications">
                    <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </button>
                {{-- Cart with badge --}}
                <a href="{{ route('cart') }}" class="relative w-11 h-11 flex items-center justify-center rounded-full bg-[#F5F5F5]" aria-label="Cart">
                    <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span id="mobile-cart-badge"
                          class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-brand-red text-white text-[10px] font-bold px-1"
                          style="{{ session('cart_count', 0) > 0 ? '' : 'display:none' }}"
                          data-cart-count>{{ session('cart_count', 0) }}</span>
                </a>
            </div>
        </div>
        {{-- Mobile search --}}
        <div class="px-4 pb-3">
            <form action="{{ route('search') }}" method="GET" class="relative">
                <input
                    type="search"
                    name="q"
                    placeholder="Search products…"
                    class="w-full h-11 pl-10 pr-4 rounded-full bg-[#F5F5F5] text-sm text-brand-text placeholder-brand-muted border-0 focus:outline-none focus:ring-2 focus:ring-brand-light-red transition"
                >
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </form>
        </div>
    </header>

    {{-- ─── Main content ──────────────────────────────────────── --}}
    <main class="pb-20 md:pb-0">
        {{ $slot }}
    </main>

    {{-- ─── Desktop Footer ────────────────────────────────────── --}}
    <footer class="hidden md:block border-t border-brand-border-light bg-white mt-16">
        <div class="max-w-[1280px] mx-auto px-14 py-6 flex items-center justify-between">
            <div>
                <span class="font-bold text-brand-text text-sm">Fenroy Supermarket</span>
                <span class="text-brand-secondary-text text-sm ml-1">— Your everyday online market</span>
            </div>
            <nav class="flex items-center gap-6 text-[13px] text-brand-secondary-text">
                <a href="{{ route('about') }}" class="hover:text-brand-text transition-colors">About</a>
                <a href="{{ route('delivery') }}" class="hover:text-brand-text transition-colors">Delivery</a>
                <a href="{{ route('faq') }}" class="hover:text-brand-text transition-colors">FAQ</a>
                <a href="{{ route('contact') }}" class="hover:text-brand-text transition-colors">Contact</a>
                <a href="{{ route('privacy') }}" class="hover:text-brand-text transition-colors">Privacy</a>
            </nav>
            <p class="text-[13px] text-brand-secondary-text">
                Powered by <a href="#" class="font-bold text-brand-red">Fenroy</a>
            </p>
        </div>
    </footer>

    {{-- ─── Mobile Bottom Nav ─────────────────────────────────── --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-brand-border-light safe-area-pb">
        <div class="flex items-stretch h-[60px]">
            {{-- Home --}}
            <a href="{{ route('home') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 min-h-[44px] {{ request()->routeIs('home') ? 'text-brand-red' : 'text-brand-secondary-text' }}">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('home') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="text-[10px] {{ request()->routeIs('home') ? 'font-bold' : 'font-medium' }}">Home</span>
            </a>
            {{-- Categories --}}
            <a href="{{ route('category.index') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 min-h-[44px] {{ request()->routeIs('category.*') ? 'text-brand-red' : 'text-brand-secondary-text' }}">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('category.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span class="text-[10px] {{ request()->routeIs('category.*') ? 'font-bold' : 'font-medium' }}">Categories</span>
            </a>
            {{-- Orders --}}
            <a href="{{ route('account.orders') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 min-h-[44px] {{ request()->routeIs('account.orders') ? 'text-brand-red' : 'text-brand-secondary-text' }}">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('account.orders') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span class="text-[10px] {{ request()->routeIs('account.orders') ? 'font-bold' : 'font-medium' }}">Orders</span>
            </a>
            {{-- Account --}}
            <a href="{{ route('account.profile') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 min-h-[44px] {{ request()->routeIs('account.*') && !request()->routeIs('account.orders') ? 'text-brand-red' : 'text-brand-secondary-text' }}">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('account.*') && !request()->routeIs('account.orders') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="text-[10px] {{ request()->routeIs('account.*') && !request()->routeIs('account.orders') ? 'font-bold' : 'font-medium' }}">Account</span>
            </a>
        </div>
    </nav>

    {{-- ─── Toast container ───────────────────────────────────── --}}
    <div id="toast-container" class="fixed bottom-20 md:bottom-6 left-4 right-4 md:left-6 md:right-auto md:w-80 z-50 flex flex-col gap-2 pointer-events-none"></div>

    @livewireScripts
    <script src="https://js.paystack.co/v1/inline.js"></script>

    {{-- PWA Service Worker --}}
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
    }
    </script>

    <script>
    function searchAutocomplete() {
        return {
            query: '',
            results: [],
            open: false,
            active: -1,
            async fetch() {
                if (this.query.length < 2) { this.results = []; this.open = false; return; }
                try {
                    const r = await window.fetch('/api/search-suggest?q=' + encodeURIComponent(this.query));
                    this.results = await r.json();
                    this.open = this.results.length > 0;
                    this.active = -1;
                } catch {}
            },
            close() { this.open = false; this.active = -1; },
            moveDown() { this.active = Math.min(this.active + 1, this.results.length - 1); },
            moveUp()   { this.active = Math.max(this.active - 1, 0); },
            selectActive() {
                if (this.active >= 0 && this.results[this.active]) {
                    window.location.href = '/products/' + this.results[this.active].slug;
                } else {
                    window.location.href = '{{ route('search') }}?q=' + encodeURIComponent(this.query);
                }
            }
        }
    }
    </script>
</body>
</html>
