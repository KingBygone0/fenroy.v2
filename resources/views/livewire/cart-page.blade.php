<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-36 md:pb-8">

    {{-- ─── Minimal checkout header (cart/checkout pages) ─── --}}
    {{-- (The full storefront header is already in the layout; this is the mobile compact version) --}}

    @if(count($items) === 0)
    {{-- ══════════════════════════════
         EMPTY CART STATE
    ══════════════════════════════ --}}
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-20 h-20 rounded-full bg-brand-light-red flex items-center justify-center mb-5">
            <svg class="w-9 h-9 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <h2 class="text-2xl font-extrabold text-brand-text mb-2">Your cart is empty</h2>
        <p class="text-sm text-brand-secondary-text mb-7 max-w-xs">Looks like you haven't added anything yet. Start browsing and find something you love.</p>
        <a href="{{ route('home') }}" class="h-12 px-8 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors cursor-pointer inline-flex items-center">
            Start Shopping
        </a>
    </div>

    @else
    {{-- ══════════════════════════════
         CART WITH ITEMS
    ══════════════════════════════ --}}

    {{-- Page title --}}
    <div class="flex items-center gap-3 mb-5 md:mb-6">
        {{-- Mobile back arrow --}}
        <a href="javascript:history.back()" class="md:hidden w-11 h-11 -ml-2 flex items-center justify-center rounded-full active:bg-[#F5F5F5]" aria-label="Back">
            <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="text-[22px] md:text-[28px] font-extrabold text-brand-text">
            Your Cart <span class="text-brand-muted font-normal text-base ml-1">· {{ count($items) }} {{ count($items) === 1 ? 'item' : 'items' }}</span>
        </h1>
    </div>

    {{-- Free-delivery meter --}}
    <div class="mb-5 md:mb-6 rounded-xl px-4 py-3 border {{ $qualifiesForFreeDelivery ? 'bg-brand-success-tint border-[#C8E6C9]' : 'bg-[#FFF9F0] border-[#FDE8C8]' }}">
        <div class="flex items-center justify-between mb-2">
            @if($qualifiesForFreeDelivery)
            <p class="text-sm font-semibold text-brand-success flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                You've qualified for free delivery!
            </p>
            @else
            <p class="text-sm font-semibold text-brand-text">
                Add <strong>GH₵ {{ number_format($amountToFreeDelivery, 2) }}</strong> more for free delivery
            </p>
            @endif
            <span class="text-xs text-brand-muted">GH₵ {{ number_format($subtotal, 2) }} / {{ number_format($minFreeThreshold, 2) }}</span>
        </div>
        <div class="h-1.5 rounded-full bg-black/10 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 {{ $qualifiesForFreeDelivery ? 'bg-brand-success' : 'bg-brand-warning' }}"
                 style="width: {{ $deliveryProgress }}%"></div>
        </div>
    </div>

    <div class="md:grid md:grid-cols-[1fr_380px] md:gap-10">

        {{-- ─── LINE ITEMS ─── --}}
        <div class="bg-white rounded-2xl border border-brand-border-light overflow-hidden mb-5 md:mb-0">
            @foreach($items as $index => $item)
            @php $hasDiscount = !empty($item['old_price']); $lineOld = $hasDiscount ? $item['old_price'] * $item['qty'] : null; @endphp
            <div class="flex items-start gap-4 px-4 md:px-5 py-5 {{ !$loop->last ? 'border-b border-brand-border-light' : '' }}"
                 wire:key="cart-item-{{ $index }}"
                 x-data="{ removing: false }"
                 x-show="!removing"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- Photo --}}
                <div class="shrink-0 w-20 h-20 rounded-xl overflow-hidden bg-[#FAFAFA] border border-brand-border-light relative">
                    @if(!empty($item['image']))
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="absolute inset-0 w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0" style="background: repeating-linear-gradient(45deg,#FAFAFA,#FAFAFA 6px,#F3F3F3 6px,#F3F3F3 12px);"></div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <a href="{{ route('product.show', $item['slug']) }}" class="text-[15px] font-semibold text-brand-text hover:text-brand-red transition-colors leading-snug line-clamp-2 cursor-pointer">{{ $item['name'] }}</a>
                    <p class="text-xs text-brand-secondary-text mt-0.5">{{ $item['unit'] }}</p>

                    <div class="flex items-center gap-4 mt-2.5 flex-wrap">
                        {{-- Actions --}}
                        <button
                            wire:click="removeItem({{ $index }})"
                            @click="removing = true"
                            class="text-[13px] text-brand-secondary-text hover:text-brand-dark-red transition-colors cursor-pointer">
                            Remove
                        </button>
                        <span class="text-brand-border-light">·</span>
                        <button class="text-[13px] text-brand-secondary-text hover:text-brand-text transition-colors cursor-pointer">Save for later</button>
                    </div>
                </div>

                {{-- Qty + price --}}
                <div class="shrink-0 flex flex-col items-end gap-3">
                    {{-- Price --}}
                    <div class="text-right">
                        <p class="text-[16px] font-extrabold text-brand-text">GH₵ {{ number_format($item['price'] * $item['qty'], 2) }}</p>
                        @if($hasDiscount)
                        <p class="text-xs text-brand-muted line-through">GH₵ {{ number_format($item['old_price'] * $item['qty'], 2) }}</p>
                        @endif
                    </div>

                    {{-- Stepper --}}
                    <div class="flex items-center h-9 md:h-10 rounded-full border border-brand-border">
                        <button wire:click="decrement({{ $index }})"
                                class="w-9 md:w-10 h-full flex items-center justify-center text-brand-text hover:text-brand-red transition-colors cursor-pointer min-w-[44px] min-h-[44px]"
                                aria-label="Decrease">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                        <span class="w-6 text-center text-sm font-bold">{{ $item['qty'] }}</span>
                        <button wire:click="increment({{ $index }})"
                                class="w-9 md:w-10 h-full flex items-center justify-center text-brand-text hover:text-brand-red transition-colors cursor-pointer min-w-[44px] min-h-[44px]"
                                aria-label="Increase">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ─── ORDER SUMMARY ─── --}}
        <div class="bg-[#F9F9F9] rounded-[18px] p-6 md:p-7 self-start sticky top-24 border border-brand-border-light">
            <h2 class="text-[17px] font-extrabold text-brand-text mb-4">Order Summary</h2>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-brand-secondary-text">Subtotal</span>
                    <span class="font-semibold">GH₵ {{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-brand-secondary-text">Delivery</span>
                    @if($qualifiesForFreeDelivery)
                    <span class="font-semibold text-brand-success">Free in select areas</span>
                    @else
                    <span class="font-semibold text-brand-muted">From GH₵ {{ number_format($minDeliveryFee, 2) }}</span>
                    @endif
                </div>
                @if($appliedCoupon && $discountAmount > 0)
                <div class="flex justify-between text-brand-success">
                    <span class="flex items-center gap-1.5">
                        Discount · {{ $appliedCoupon }}
                        <button wire:click="removeCoupon" class="text-brand-muted hover:text-brand-danger transition-colors cursor-pointer text-xs" aria-label="Remove coupon">✕</button>
                    </span>
                    <span class="font-semibold">−GH₵ {{ number_format($discountAmount, 2) }}</span>
                </div>
                @elseif($appliedCoupon)
                <div class="flex justify-between text-brand-success">
                    <span class="flex items-center gap-1.5">
                        Coupon · {{ $appliedCoupon }}
                        <button wire:click="removeCoupon" class="text-brand-muted hover:text-brand-danger transition-colors cursor-pointer text-xs" aria-label="Remove coupon">✕</button>
                    </span>
                    <span class="font-semibold text-brand-muted text-xs">applied</span>
                </div>
                @endif
            </div>

            {{-- Coupon input --}}
            <div class="mt-4">
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="couponInput"
                        wire:keydown.enter="applyCoupon"
                        placeholder="Coupon code"
                        class="flex-1 h-11 px-4 rounded-full bg-white border border-brand-input-border text-sm focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition placeholder-brand-muted"
                    >
                    <button wire:click="applyCoupon"
                            class="h-11 px-5 rounded-full bg-brand-text hover:bg-black text-white text-sm font-semibold transition-colors cursor-pointer whitespace-nowrap">
                        Apply
                    </button>
                </div>
                @if($couponError)
                <p class="text-xs text-brand-danger mt-1.5 pl-1" role="alert">{{ $couponError }}</p>
                @endif
                @if($appliedCoupon)
                <p class="text-xs text-brand-success mt-1.5 pl-1">✓ Code applied</p>
                @endif
                <p class="text-[11px] text-brand-muted mt-1.5 pl-1">Have a promo code? Enter it above.</p>
            </div>

            <div class="border-t border-brand-border my-4"></div>

            <div class="flex justify-between items-center mb-5">
                <span class="text-base font-extrabold">Total</span>
                <span class="text-[24px] font-extrabold text-brand-text">GH₵ {{ number_format($total, 2) }}</span>
            </div>

            <a href="{{ route('checkout') }}"
               class="w-full h-[50px] flex items-center justify-center rounded-full bg-brand-red hover:bg-brand-dark-red text-white font-semibold text-sm transition-colors cursor-pointer">
                Proceed to Checkout
            </a>

            <a href="{{ route('home') }}" class="mt-3 w-full flex items-center justify-center text-sm text-brand-secondary-text hover:text-brand-text transition-colors">
                ← Continue shopping
            </a>
        </div>
    </div>

    {{-- ── Mobile sticky footer ── --}}
    <div class="md:hidden fixed bottom-[60px] inset-x-0 z-40 bg-white border-t border-brand-border-light px-4 py-3">
        <div class="flex items-center justify-between mb-2.5">
            <span class="text-sm text-brand-secondary-text">Total</span>
            <span class="text-xl font-extrabold">GH₵ {{ number_format($total, 2) }}</span>
        </div>
        <a href="{{ route('checkout') }}"
           class="w-full h-12 flex items-center justify-center rounded-full bg-brand-red active:bg-brand-dark-red text-white font-semibold text-sm transition-colors cursor-pointer">
            Proceed to Checkout
        </a>
    </div>

    @endif
</div>
