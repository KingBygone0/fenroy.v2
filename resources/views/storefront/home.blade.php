<x-layouts.storefront title="Fenroy — Fresh Groceries Delivered">

    {{-- ════════════════════════════════════════════════════════════
         DESKTOP HERO  (≥ md)
    ════════════════════════════════════════════════════════════ --}}
    <section class="hidden md:block max-w-[1280px] mx-auto px-14 py-12">
        <div class="grid grid-cols-2 gap-12 items-center">

            {{-- Left column --}}
            <div class="flex flex-col gap-6">
                <h1 class="text-[48px] font-extrabold leading-[1.1] tracking-[-0.025em] text-brand-text">
                    Everything you need,<br>delivered to your door.
                </h1>
                <p class="text-base text-brand-secondary-text leading-relaxed max-w-md">
                    Shop fresh groceries, household essentials and more — delivered same-day to your door anywhere in Accra.
                </p>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('category.index') }}"
                       class="inline-flex items-center h-12 px-7 rounded-full bg-brand-red hover:bg-brand-dark-red text-white font-semibold text-sm transition-colors duration-150 cursor-pointer">
                        Start Shopping
                    </a>
                    <a href="{{ route('deals') }}"
                       class="inline-flex items-center h-12 px-7 rounded-full border-2 border-brand-text text-brand-text hover:border-brand-red hover:text-brand-red font-semibold text-sm transition-colors duration-150 cursor-pointer">
                        Today's Deals
                    </a>
                </div>
                <p class="text-[13px] text-brand-secondary-text flex items-center gap-4">
                    <span>⚡ 3h delivery</span>
                    <span>·</span>
                    <span>2,400+ products</span>
                    <span>·</span>
                    <span>4.8★ rated</span>
                </p>
            </div>

            {{-- Right column — hero image --}}
            <div class="relative flex items-center justify-end">
                <div class="relative w-full max-w-[420px]">
                    <img
                        src="{{ asset('images/hero-basket.png') }}"
                        alt="Fresh grocery basket"
                        class="w-full h-[380px] object-contain"
                        style="mask-image: radial-gradient(ellipse 66% 66% at 50% 52%, black 55%, transparent 80%); -webkit-mask-image: radial-gradient(ellipse 66% 66% at 50% 52%, black 55%, transparent 80%);"
                    >
                    {{-- Floating "Order before 6pm" card --}}
                    <div class="absolute bottom-6 left-0 bg-white rounded-xl px-4 py-3" style="box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                        <p class="text-[13px] font-semibold text-brand-text">Order before 6pm</p>
                        <p class="text-[12px] text-brand-secondary-text">for same-day delivery</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         MOBILE HERO  (< md)
    ════════════════════════════════════════════════════════════ --}}
    <section class="md:hidden px-4 pt-4">
        <div class="bg-brand-dark-red rounded-2xl p-6 relative overflow-hidden">
            {{-- Decorative circle --}}
            <div class="absolute -bottom-8 -right-8 w-36 h-36 rounded-full bg-white/10"></div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#FFCDD2] mb-2">Fresh Groceries</p>
            <h2 class="text-[21px] font-extrabold text-white leading-tight mb-4">
                Everything you need,<br>delivered fast.
            </h2>
            <a href="{{ route('category.index') }}" class="inline-flex items-center h-10 px-6 rounded-full bg-white text-brand-dark-red font-semibold text-sm cursor-pointer hover:bg-brand-light-red transition-colors">
                Shop Now
            </a>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         SHOP BY CATEGORY
    ════════════════════════════════════════════════════════════ --}}
    <section class="max-w-[1280px] mx-auto px-4 md:px-14 mt-10 md:mt-14">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h2 class="text-[22px] md:text-[26px] font-extrabold text-brand-text">Shop by Category</h2>
            <a href="{{ route('category.index') }}" class="text-sm font-semibold text-brand-dark-red hover:underline">All categories →</a>
        </div>

        {{-- Desktop: 8-col / Mobile: 4-col --}}
        <div class="grid grid-cols-4 md:grid-cols-8 gap-2 md:gap-[14px]">
            @foreach($categories as $cat)
            <a href="{{ route('category.show', $cat['slug']) }}"
               class="group flex flex-col items-center gap-2 md:gap-3 p-3 md:p-[18px_10px] bg-white border border-brand-border-light rounded-2xl hover:border-brand-red hover:shadow-hover transition-all duration-150 cursor-pointer">
                <img src="{{ asset('images/' . $cat['image']) }}"
                     alt="{{ $cat['name'] }}"
                     class="w-14 h-14 md:w-14 md:h-14 rounded-full object-cover">
                <span class="text-[10px] md:text-[12px] font-semibold text-brand-text text-center leading-tight">{{ $cat['name'] }}</span>
            </a>
            @endforeach
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         FEATURED THIS WEEK
    ════════════════════════════════════════════════════════════ --}}
    <section class="max-w-[1280px] mx-auto px-4 md:px-14 mt-10 md:mt-14">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h2 class="text-[22px] md:text-[26px] font-extrabold text-brand-text">Featured this Week</h2>
            <a href="{{ route('category.index') }}" class="text-sm font-semibold text-brand-dark-red hover:underline">See all →</a>
        </div>

        {{-- 2-col mobile / 4-col desktop --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
            @foreach($featuredProducts as $product)
            <x-storefront.product-card :product="$product" />
            @endforeach
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         PROMO BAND
    ════════════════════════════════════════════════════════════ --}}
    <section class="max-w-[1280px] mx-auto px-4 md:px-14 mt-10 md:mt-14">
        <div class="bg-brand-light-red rounded-2xl px-6 md:px-11 py-8 md:py-9 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h3 class="text-2xl md:text-[24px] font-extrabold text-brand-dark-red">Up to 30% off today's deals</h3>
                <p class="text-sm text-[#8A5A5A] mt-1">Fresh discounts on hundreds of products — every day.</p>
            </div>
            <a href="{{ route('deals') }}"
               class="inline-flex items-center h-11 px-7 rounded-full bg-brand-red hover:bg-brand-dark-red text-white font-semibold text-sm transition-colors duration-150 cursor-pointer whitespace-nowrap">
                Shop Deals
            </a>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         BEST SELLERS
    ════════════════════════════════════════════════════════════ --}}
    <section class="max-w-[1280px] mx-auto px-4 md:px-14 mt-10 md:mt-14">
        <div class="flex items-center justify-between mb-4 md:mb-6">
            <h2 class="text-[22px] md:text-[26px] font-extrabold text-brand-text">Best Sellers</h2>
            <a href="{{ route('best-sellers') }}" class="text-sm font-semibold text-brand-dark-red hover:underline">See all →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
            @foreach($bestSellers as $product)
            <x-storefront.product-card :product="$product" />
            @endforeach
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════════
         TRUST STRIP
    ════════════════════════════════════════════════════════════ --}}
    <section class="max-w-[1280px] mx-auto px-4 md:px-14 mt-10 md:mt-14 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['icon' => 'truck', 'title' => 'Fast Delivery', 'sub' => 'Same-day in Accra'],
                ['icon' => 'shield', 'title' => 'Secure Payments', 'sub' => 'MoMo, card & more'],
                ['icon' => 'tag', 'title' => 'Great Prices', 'sub' => 'Daily deals & offers'],
                ['icon' => 'headphones', 'title' => '24/7 Support', 'sub' => 'We\'re here to help'],
            ] as $trust)
            <div class="flex flex-col items-center text-center gap-2 bg-white rounded-2xl p-5 border border-brand-border-light">
                <div class="w-11 h-11 rounded-full bg-brand-light-red flex items-center justify-center">
                    @if($trust['icon'] === 'truck')
                    <svg class="w-5 h-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    @elseif($trust['icon'] === 'shield')
                    <svg class="w-5 h-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @elseif($trust['icon'] === 'tag')
                    <svg class="w-5 h-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    @else
                    <svg class="w-5 h-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    @endif
                </div>
                <p class="text-[14px] font-semibold text-brand-text">{{ $trust['title'] }}</p>
                <p class="text-[12px] text-brand-secondary-text">{{ $trust['sub'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

</x-layouts.storefront>
