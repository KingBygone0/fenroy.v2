<div x-data="{ tab: 'description', image: 0 }">

    @php
        $hasDiscount = !empty($product['old_price']) && $product['old_price'] > $product['price'];
        $saveAmount  = $hasDiscount ? ($product['old_price'] - $product['price']) : 0;
        $inStock     = $product['stock'] > 0;
    @endphp

    <div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-28 md:pb-8">

        {{-- ─── Desktop breadcrumb ─── --}}
        <nav class="hidden md:block text-xs text-brand-muted mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('category.show', 'fruits-vegetables') }}" class="hover:text-brand-text transition-colors">Fruits &amp; Vegetables</a>
            <span class="mx-1">/</span>
            <span class="font-bold text-brand-text">{{ $product['name'] }}</span>
        </nav>

        {{-- ─── Mobile icon header ─── --}}
        <div class="md:hidden flex items-center justify-between mb-3">
            <a href="javascript:history.back()" class="w-11 h-11 -ml-2 flex items-center justify-center rounded-full active:bg-[#F5F5F5]" aria-label="Back">
                <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div class="flex items-center gap-1">
                <button wire:click="toggleWishlist" class="w-11 h-11 flex items-center justify-center rounded-full active:bg-[#F5F5F5] cursor-pointer" aria-label="Wishlist">
                    <svg class="w-5 h-5 {{ $wishlisted ? 'text-brand-red' : 'text-brand-text' }}" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
                <a href="{{ route('cart') }}" class="relative w-11 h-11 flex items-center justify-center rounded-full active:bg-[#F5F5F5]" aria-label="Cart">
                    <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    @if(session('cart_count', 0) > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-brand-red text-white text-[10px] font-bold px-1">{{ session('cart_count') }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- ═══════════ MAIN 2-COL (desktop) / stacked (mobile) ═══════════ --}}
        <div class="md:grid md:grid-cols-2 md:gap-12">

            {{-- ─── LEFT: gallery ─── --}}
            <div x-data="{ images: {{ json_encode($product['images'] ?? [$product['image']]) }} }">
                <div class="relative bg-white rounded-[18px] border border-brand-border-light overflow-hidden h-[260px] md:h-[420px] flex items-center justify-center">
                    {{-- Fallback striped bg shown while image loads --}}
                    <div class="absolute inset-0" style="background: repeating-linear-gradient(45deg, #FAFAFA, #FAFAFA 12px, #F3F3F3 12px, #F3F3F3 24px);"></div>

                    {{-- Actual product image --}}
                    <img :src="images[image] ?? images[0]"
                         alt="{{ e($product['name']) }}"
                         class="relative z-10 max-h-full max-w-full object-contain p-4"
                         onerror="this.style.display='none'">

                    @if($hasDiscount)
                    <span class="absolute top-3 left-3 z-20 flex items-center h-6 px-2.5 rounded-full bg-brand-red text-white text-[11px] font-bold uppercase tracking-wide">
                        {{ round($saveAmount / $product['old_price'] * 100) }}% off
                    </span>
                    @endif

                    {{-- Desktop wishlist on image --}}
                    <button wire:click="toggleWishlist"
                            class="hidden md:flex absolute top-3 right-3 z-20 w-10 h-10 items-center justify-center rounded-full bg-white shadow-card hover:shadow-hover transition cursor-pointer"
                            aria-label="Add to wishlist">
                        <svg class="w-5 h-5 {{ $wishlisted ? 'text-brand-red' : 'text-brand-secondary-text' }}" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                </div>

                {{-- Thumbnails (desktop) — only show if multiple images --}}
                @if(count($product['images'] ?? [$product['image']]) > 1)
                <div class="hidden md:flex gap-3 mt-4">
                    @foreach(($product['images'] ?? [$product['image']]) as $i => $imgUrl)
                    <button
                        @click="image = {{ $i }}"
                        class="relative w-[76px] h-[76px] rounded-xl overflow-hidden border-2 transition-colors cursor-pointer bg-white"
                        :class="image === {{ $i }} ? 'border-brand-red' : 'border-transparent hover:border-brand-border'">
                        <img src="{{ $imgUrl }}" alt="View {{ $i + 1 }}" class="w-full h-full object-contain p-1">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ─── RIGHT: info ─── --}}
            <div class="mt-4 md:mt-0">

                {{-- Stock + SKU --}}
                <div class="flex items-center gap-3 mb-2">
                    @if($inStock)
                    <span class="inline-flex items-center gap-1.5 h-6 px-2.5 rounded-full bg-brand-success-tint text-brand-success text-[11px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-success"></span> In stock
                    </span>
                    @else
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full bg-[#F5F5F5] text-brand-muted text-[11px] font-bold">Out of stock</span>
                    @endif
                    <span class="text-xs text-brand-muted">SKU: {{ $product['sku'] }}</span>
                </div>

                {{-- Title --}}
                <h1 class="text-[19px] md:text-[30px] font-extrabold tracking-tight text-brand-text leading-tight">{{ $product['name'] }}</h1>

                {{-- Rating --}}
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex items-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= round($product['rating']) ? 'text-amber-400' : 'text-brand-border' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <span class="text-sm font-bold text-brand-text">{{ $product['rating'] }}</span>
                    <span class="text-sm text-brand-muted">· {{ $product['rating_count'] }} ratings</span>
                </div>

                {{-- Price row --}}
                <div class="flex items-center gap-3 mt-4 flex-wrap">
                    <span class="text-[24px] md:text-[32px] font-extrabold text-brand-text">GH₵ {{ number_format($product['price'], 2) }}</span>
                    @if($hasDiscount)
                    <span class="text-base text-brand-muted line-through">GH₵ {{ number_format($product['old_price'], 2) }}</span>
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full bg-brand-light-red text-brand-dark-red text-[11px] font-bold">
                        Save GH₵ {{ number_format($saveAmount, 2) }}
                    </span>
                    @endif
                </div>
                <p class="text-[13px] text-brand-secondary-text mt-1">
                    {{ $product['unit'] }}{{ $product['unit_note'] ? ' · ' . $product['unit_note'] : '' }}
                </p>

                {{-- ─── Desktop buy row ─── --}}
                <div class="hidden md:flex items-center gap-3 mt-6">
                    {{-- Qty stepper --}}
                    <div class="flex items-center h-12 rounded-full border border-brand-border bg-white">
                        <button wire:click="decrement"
                                class="w-12 h-12 flex items-center justify-center rounded-full text-brand-text hover:text-brand-red transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-default"
                                @disabled($qty <= 1) aria-label="Decrease quantity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                        <span class="w-8 text-center text-sm font-bold">{{ $qty }}</span>
                        <button wire:click="increment"
                                class="w-12 h-12 flex items-center justify-center rounded-full text-brand-text hover:text-brand-red transition-colors cursor-pointer" aria-label="Increase quantity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                    </div>

                    {{-- Add to Cart --}}
                    <button wire:click="addToCart"
                            wire:loading.attr="disabled"
                            wire:target="addToCart"
                            @disabled(!$inStock)
                            class="flex-1 h-12 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors cursor-pointer disabled:opacity-45 disabled:cursor-default flex items-center justify-center gap-2">
                        <svg wire:loading wire:target="addToCart" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span wire:loading.remove wire:target="addToCart">Add to Cart · GH₵ {{ number_format($lineTotal, 2) }}</span>
                        <span wire:loading wire:target="addToCart">Adding…</span>
                    </button>

                    {{-- Buy Now --}}
                    <a href="{{ route('checkout') }}"
                       class="h-12 px-7 rounded-full bg-brand-text hover:bg-black text-white text-sm font-semibold transition-colors cursor-pointer flex items-center">
                        Buy Now
                    </a>
                </div>

                {{-- Reassurance card --}}
                <div class="mt-6 border border-brand-border-light rounded-[14px] p-4 space-y-2.5 bg-white">
                    <p class="flex items-center gap-2.5 text-[13px] text-brand-text">
                        <svg class="w-4 h-4 text-brand-success shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Order before <strong>6pm</strong> for same-day delivery in Accra
                    </p>
                    <p class="flex items-center gap-2.5 text-[13px] text-brand-text">
                        <svg class="w-4 h-4 text-brand-success shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Easy returns — report any issue within 24 hours for a refund
                    </p>
                    <p class="flex items-center gap-2.5 text-[13px] text-brand-text">
                        <svg class="w-4 h-4 text-brand-success shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Pay securely with MoMo or card on delivery or online
                    </p>
                </div>
            </div>
        </div>

        {{-- ═══════════ TABS ═══════════ --}}
        <div class="mt-8 md:mt-12">
            <div class="flex gap-6 border-b border-brand-border">
                @foreach(['description' => 'Description', 'info' => 'Product info', 'delivery' => 'Delivery'] as $key => $label)
                <button
                    @click="tab = '{{ $key }}'"
                    class="pb-3 text-sm font-semibold border-b-2 -mb-px transition-colors cursor-pointer min-h-[44px]"
                    :class="tab === '{{ $key }}' ? 'border-brand-red text-brand-dark-red' : 'border-transparent text-brand-secondary-text hover:text-brand-text'">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <div class="py-5 text-sm leading-[1.7] text-[#444] max-w-3xl">
                <div x-show="tab === 'description'">
                    <p>{{ $product['description'] }}</p>
                </div>
                <div x-show="tab === 'info'" x-cloak>
                    <dl class="grid grid-cols-[140px_1fr] gap-y-2.5">
                        @foreach($product['info'] as $key => $value)
                        <dt class="font-semibold text-brand-text">{{ $key }}</dt>
                        <dd>{{ $value }}</dd>
                        @endforeach
                    </dl>
                </div>
                <div x-show="tab === 'delivery'" x-cloak>
                    <p class="mb-2">Same-day delivery across Accra for orders placed before <strong>6pm</strong>. Orders after cutoff arrive next morning.</p>
                    <p class="mb-2">Delivery fee depends on your zone — from <strong>GH₵ 10.00</strong>. Free delivery on orders above <strong>GH₵ 300.00</strong>.</p>
                    <p>Our riders call ahead. If items arrive damaged or spoiled, report within 24 hours for a full refund.</p>
                </div>
            </div>
        </div>

        {{-- ═══════════ CUSTOMER REVIEWS ═══════════ --}}
        <div class="mt-6 md:mt-10">
            <h2 class="text-[20px] md:text-[26px] font-extrabold text-brand-text mb-4 md:mb-6">
                Customer reviews
                @if(!empty($product['reviews']))
                <span class="text-base font-normal text-brand-secondary-text ml-2">({{ count($product['reviews']) }})</span>
                @endif
            </h2>

            {{-- Existing reviews --}}
            @if(!empty($product['reviews']))
            <div class="space-y-4 mb-6">
                @foreach($product['reviews'] as $rev)
                <div class="bg-white rounded-2xl border border-brand-border-light p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-full bg-brand-light-red flex items-center justify-center text-brand-red font-bold text-sm shrink-0">
                            {{ strtoupper(substr($rev['name'], 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-brand-text">{{ $rev['name'] }}</p>
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $rev['rating'] ? 'text-amber-400' : 'text-brand-border' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                            </div>
                        </div>
                        <span class="ml-auto text-xs text-brand-muted">{{ $rev['date'] }}</span>
                    </div>
                    @if($rev['body'])
                    <p class="text-sm text-brand-secondary-text leading-relaxed">{{ $rev['body'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 mb-6 bg-white rounded-2xl border border-brand-border-light text-brand-secondary-text">
                <svg class="w-8 h-8 mx-auto mb-2 text-brand-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <p class="text-sm font-medium">No reviews yet — be the first!</p>
            </div>
            @endif

            {{-- Write a review --}}
            <livewire:product-review-form :product-slug="$slug" />
        </div>

        {{-- ═══════════ FREQUENTLY BOUGHT TOGETHER ═══════════ --}}
        <div class="mt-6 md:mt-10">
            <h2 class="text-[20px] md:text-[26px] font-extrabold text-brand-text mb-4 md:mb-6">Frequently bought together</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
                @foreach($related as $rel)
                <x-storefront.product-card :product="$rel" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════ MOBILE STICKY BOTTOM BAR ═══════════ --}}
    <div class="md:hidden fixed bottom-[60px] inset-x-0 z-40 bg-white border-t border-brand-border-light px-4 py-3 flex items-center gap-3">
        {{-- Qty stepper --}}
        <div class="flex items-center h-12 rounded-full border border-brand-border bg-white shrink-0">
            <button wire:click="decrement"
                    class="w-11 h-12 flex items-center justify-center text-brand-text disabled:opacity-40 cursor-pointer" @disabled($qty <= 1) aria-label="Decrease quantity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
            <span class="w-7 text-center text-sm font-bold">{{ $qty }}</span>
            <button wire:click="increment"
                    class="w-11 h-12 flex items-center justify-center text-brand-text cursor-pointer" aria-label="Increase quantity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        </div>

        {{-- Add to Cart --}}
        <button wire:click="addToCart"
                wire:loading.attr="disabled"
                wire:target="addToCart"
                @disabled(!$inStock)
                class="flex-1 h-12 rounded-full bg-brand-red active:bg-brand-dark-red text-white text-sm font-semibold transition-colors cursor-pointer disabled:opacity-45 flex items-center justify-center gap-2">
            <svg wire:loading wire:target="addToCart" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span wire:loading.remove wire:target="addToCart">Add to Cart · GH₵ {{ number_format($lineTotal, 2) }}</span>
            <span wire:loading wire:target="addToCart">Adding…</span>
        </button>
    </div>

    {{-- ═══════════ TOAST ═══════════ --}}
    <div
        x-data="{ show: false, message: '' }"
        x-on:toast.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-32 md:bottom-6 left-4 right-4 md:left-6 md:right-auto z-[70] md:w-96">
        <div class="bg-brand-text text-white rounded-xl px-4 py-3.5 flex items-center justify-between gap-3" style="box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <span class="text-sm font-medium" x-text="message"></span>
            <a href="{{ route('cart') }}" class="text-sm font-bold text-[#FFCDD2] hover:text-white whitespace-nowrap transition-colors">View cart</a>
        </div>
    </div>
</div>
