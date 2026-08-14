@props(['product'])

@php
    $hasDiscount = !empty($product['old_price']) && $product['old_price'] > $product['price'];
    $isOutOfStock = ($product['stock'] ?? 1) === 0;
    $isLowStock   = !$isOutOfStock && ($product['stock'] ?? 99) <= 5;
@endphp

<div class="group bg-white rounded-[16px] border border-brand-border-light hover:shadow-hover transition-all duration-150 flex flex-col overflow-hidden"
     style="{{ $isOutOfStock ? 'opacity:0.7' : '' }}">

    {{-- Image area --}}
    <div class="relative pt-[100%] bg-[#FAFAFA]">
        <img
            src="{{ isset($product['image']) && $product['image'] ? $product['image'] : asset('images/products/bananas.jpg') }}"
            alt="{{ $product['name'] }}"
            class="absolute inset-0 w-full h-full object-contain p-3 {{ $isOutOfStock ? 'grayscale opacity-55' : '' }}"
            loading="lazy"
        >

        {{-- Discount badge --}}
        @if($hasDiscount && !$isOutOfStock)
        <span class="absolute top-2 left-2 flex items-center h-5 px-2 rounded-full bg-brand-red text-white text-[11px] font-bold uppercase tracking-wide">
            {{ round((1 - $product['price'] / $product['old_price']) * 100) }}% off
        </span>
        @endif

        {{-- Low-stock badge --}}
        @if($isLowStock)
        <span class="absolute top-2 {{ $hasDiscount ? 'left-14' : 'left-2' }} flex items-center h-5 px-2 rounded-full bg-[#FFF7E6] text-[#B45309] text-[11px] font-semibold">
            Low stock
        </span>
        @endif

        {{-- Out-of-stock badge --}}
        @if($isOutOfStock)
        <span class="absolute top-2 left-2 flex items-center h-5 px-2 rounded-full bg-[#F5F5F5] text-brand-muted text-[11px] font-semibold">
            Out of stock
        </span>
        @endif

        {{-- Wishlist button --}}
        <button
            class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center rounded-full bg-white shadow-card hover:shadow-hover transition cursor-pointer opacity-0 group-hover:opacity-100 md:opacity-100"
            aria-label="Add to wishlist">
            <svg class="w-4 h-4 text-brand-secondary-text hover:text-brand-red transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </button>

        {{-- Quick-add button --}}
        @unless($isOutOfStock)
        <button
            x-data="{
                loading: false,
                async add() {
                    if (this.loading) return;
                    this.loading = true;
                    try {
                        const r = await fetch('/cart/quick-add', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({
                                slug:      {{ json_encode($product['slug'] ?? '') }},
                                name:      {{ json_encode($product['name']) }},
                                unit:      {{ json_encode($product['unit'] ?? '') }},
                                price:     {{ $product['price'] }},
                                old_price: {{ isset($product['old_price']) && $product['old_price'] ? $product['old_price'] : 'null' }},
                                image:     {{ json_encode($product['image'] ?? '') }}
                            })
                        });
                        const j = await r.json();
                        if (j.status === 'ok') {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: j.message } }));
                            document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = j.count);
                            document.querySelectorAll('[data-cart-total]').forEach(el => el.textContent = 'GH₳ ' + j.total);
                            var mb = document.getElementById('mobile-cart-badge');
                            if (mb) { mb.textContent = j.count; mb.style.display = ''; }
                        }
                    } finally { this.loading = false; }
                }
            }"
            @click.stop="add()"
            :class="loading ? 'opacity-60 cursor-wait' : ''"
            class="absolute bottom-2 right-2 w-9 h-9 md:w-[38px] md:h-[38px] flex items-center justify-center rounded-full bg-brand-text hover:bg-brand-red text-white transition-colors duration-150 shadow-card cursor-pointer"
            aria-label="Add {{ $product['name'] }} to cart">
            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </button>
        @endunless
    </div>

    {{-- Info --}}
    <div class="p-3 flex flex-col gap-1 flex-1">
        <a href="{{ route('product.show', $product['slug'] ?? '#') }}" class="text-[14px] font-semibold text-brand-text leading-snug hover:text-brand-red transition-colors line-clamp-2 cursor-pointer">
            {{ $product['name'] }}
        </a>
        <p class="text-[12px] text-brand-secondary-text">{{ $product['unit'] ?? '' }}</p>

        <div class="mt-auto pt-2 flex items-center gap-2 flex-wrap">
            <span class="text-[{{ $isOutOfStock ? '14px' : '15px' }}] font-extrabold {{ $isOutOfStock ? 'text-brand-muted' : 'text-brand-text' }}">
                GH₵ {{ number_format($product['price'], 2) }}
            </span>
            @if($hasDiscount)
            <span class="text-[12px] text-brand-muted line-through">GH₵ {{ number_format($product['old_price'], 2) }}</span>
            @endif
        </div>
    </div>
</div>
