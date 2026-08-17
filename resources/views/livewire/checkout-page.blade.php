<div>
{{-- ════════════════════════════════════════════════════════
     CHECKOUT FORM
════════════════════════════════════════════════════════ --}}
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-36 md:pb-12">

    {{-- ── Progress bar ── --}}
    <div class="flex items-center gap-2 mb-6 md:mb-8 text-[13px] font-semibold">
        <span class="flex items-center gap-1.5 text-brand-success">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Cart
        </span>
        <span class="text-brand-border mx-1">──</span>
        <span class="text-brand-dark-red">Checkout</span>
        <span class="text-brand-border mx-1">──</span>
        <span class="text-brand-muted">Payment</span>
        <div class="ml-auto hidden md:flex items-center gap-1.5 text-brand-secondary-text text-xs font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Secure checkout
        </div>
    </div>

    <div class="md:grid md:grid-cols-[1fr_380px] md:gap-10">

        {{-- ════════════ LEFT — numbered cards ════════════ --}}
        <div class="space-y-4 md:space-y-5">

            {{-- ── Card 1: Contact ── --}}
            <div class="bg-white rounded-[18px] border border-brand-border-light p-6 md:p-7">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-full bg-brand-text text-white text-sm font-bold flex items-center justify-center shrink-0">1</span>
                    <h2 class="text-[17px] font-extrabold">Contact</h2>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-brand-text mb-1.5">Full name <span class="text-brand-danger">*</span></label>
                        <input
                            type="text"
                            wire:model.blur="name"
                            autocomplete="name"
                            placeholder="e.g. Kofi Mensah"
                            class="w-full h-11 px-4 rounded-[10px] border text-sm transition
                                   {{ $errors->has('name') ? 'border-brand-danger focus:ring-brand-danger/20' : 'border-brand-input-border focus:border-brand-red focus:ring-brand-light-red' }}
                                   focus:outline-none focus:ring-[3px]"
                        >
                        @error('name') <p class="text-xs text-brand-danger mt-1.5" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[13px] font-semibold text-brand-text mb-1.5">Phone <span class="text-brand-danger">*</span></label>
                            <input
                                type="tel"
                                wire:model.blur="phone"
                                autocomplete="tel"
                                placeholder="e.g. 0244 000 000"
                                class="w-full h-11 px-4 rounded-[10px] border text-sm transition
                                       {{ $errors->has('phone') ? 'border-brand-danger focus:ring-brand-danger/20' : 'border-brand-input-border focus:border-brand-red focus:ring-brand-light-red' }}
                                       focus:outline-none focus:ring-[3px]"
                            >
                            @error('phone') <p class="text-xs text-brand-danger mt-1.5" role="alert">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-brand-text mb-1.5">Email <span class="text-brand-danger">*</span><span class="text-brand-muted font-normal ml-1">(for receipts)</span></label>
                            <input
                                type="email"
                                wire:model.blur="email"
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="w-full h-11 px-4 rounded-[10px] border text-sm transition
                                       {{ $errors->has('email') ? 'border-brand-danger focus:ring-brand-danger/20' : 'border-brand-input-border focus:border-brand-red focus:ring-brand-light-red' }}
                                       focus:outline-none focus:ring-[3px]"
                            >
                            @error('email') <p class="text-xs text-brand-danger mt-1.5" role="alert">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Card 2: Delivery ── --}}
            <div class="bg-white rounded-[18px] border border-brand-border-light p-6 md:p-7">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-full bg-brand-text text-white text-sm font-bold flex items-center justify-center shrink-0">2</span>
                    <h2 class="text-[17px] font-extrabold">Delivery</h2>
                </div>

                {{-- Delivery zone selector --}}
                <div class="mb-5">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">
                        Delivery area <span class="text-brand-danger">*</span>
                    </p>
                    <div class="space-y-2">
                        @foreach($zones as $zone)
                        <button
                            wire:click="$set('zoneId', {{ $zone->id }})"
                            type="button"
                            class="w-full text-left flex items-center justify-between px-4 py-3 rounded-xl border-2 transition-colors cursor-pointer
                                   {{ $zoneId == $zone->id ? 'border-brand-red bg-brand-light-red/40' : 'border-[#EBEBEB] hover:border-brand-text/20' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full border-2 shrink-0 flex items-center justify-center
                                            {{ $zoneId == $zone->id ? 'border-brand-red' : 'border-[#C8C8C8]' }}">
                                    @if($zoneId == $zone->id)
                                    <div class="w-2 h-2 rounded-full bg-brand-red"></div>
                                    @endif
                                </div>
                                <span class="text-[14px] font-semibold text-brand-text">{{ $zone->name }}</span>
                            </div>
                            <div class="text-right shrink-0 ml-3">
                                @if($zone->free_above && $subtotal >= $zone->free_above)
                                    <span class="text-[13px] font-bold text-brand-success">Free</span>
                                @else
                                    <span class="text-[13px] font-bold text-brand-text">GH₵ {{ number_format($zone->fee, 2) }}</span>
                                    @if($zone->free_above)
                                    <p class="text-[11px] text-brand-muted leading-none mt-0.5">Free above GH₵ {{ number_format($zone->free_above, 0) }}</p>
                                    @endif
                                @endif
                            </div>
                        </button>
                        @endforeach
                    </div>
                    @error('zoneId') <p class="text-xs text-brand-danger mt-2" role="alert">{{ $message }}</p> @enderror
                </div>

                {{-- Delivery address --}}
                <div class="mb-5">
                    <label class="block text-[13px] font-semibold text-brand-text mb-1.5">Delivery address <span class="text-brand-danger">*</span></label>
                    <input
                        type="text"
                        wire:model.blur="address"
                        autocomplete="street-address"
                        placeholder="e.g. House 5, New Suame Magazine Road, Kumasi"
                        class="w-full h-11 px-4 rounded-[10px] border text-sm transition
                               {{ $errors->has('address') ? 'border-brand-danger focus:ring-brand-danger/20' : 'border-brand-input-border focus:border-brand-red focus:ring-brand-light-red' }}
                               focus:outline-none focus:ring-[3px]"
                    >
                    @error('address') <p class="text-xs text-brand-danger mt-1.5" role="alert">{{ $message }}</p> @enderror
                    @auth
                    <p class="text-[12px] text-brand-muted mt-1.5">
                        <a href="{{ route('account.addresses') }}" class="text-brand-red hover:underline">Manage saved addresses</a>
                    </p>
                    @endauth
                </div>

                {{-- Delivery window --}}
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Delivery window</p>
                <div class="flex flex-wrap gap-2 mb-5">
                    @foreach(['morning' => '8am – 12pm', 'afternoon' => '12pm – 4pm', 'evening' => '4pm – 8pm'] as $key => $label)
                    <button
                        wire:click="$set('deliveryWindow', '{{ $key }}')"
                        class="h-10 px-5 rounded-full text-sm font-semibold transition-colors cursor-pointer min-w-[44px]
                               {{ $deliveryWindow === $key ? 'bg-brand-text text-white' : 'bg-white border border-brand-border text-brand-text hover:border-brand-text' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>

                {{-- Substitution preference --}}
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">If an item is unavailable</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['call' => 'Call me first', 'allow' => 'Allow substitutions', 'none' => 'No substitutions'] as $key => $label)
                    <button
                        wire:click="$set('substitution', '{{ $key }}')"
                        class="h-10 px-5 rounded-full text-sm font-semibold transition-colors cursor-pointer
                               {{ $substitution === $key ? 'bg-brand-light-red text-brand-dark-red border border-brand-red' : 'bg-white border border-brand-border text-brand-text hover:border-brand-text' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- ── Card 3: Payment ── --}}
            <div class="bg-white rounded-[18px] border border-brand-border-light p-6 md:p-7">
                <div class="flex items-center gap-3 mb-5">
                    <span class="w-7 h-7 rounded-full bg-brand-text text-white text-sm font-bold flex items-center justify-center shrink-0">3</span>
                    <h2 class="text-[17px] font-extrabold">Payment</h2>
                </div>

                <div class="space-y-2.5">
                    @foreach([
                        ['id' => 'mtn',       'label' => 'MTN Mobile Money',    'sub' => 'A payment prompt will be sent to your phone', 'tag' => 'MoMo',  'tagColor' => 'bg-yellow-100 text-yellow-800'],
                        ['id' => 'vodafone',  'label' => 'Vodafone Cash',       'sub' => 'Confirm on the Vodafone Cash app',             'tag' => 'Cash',  'tagColor' => 'bg-red-100 text-red-800'],
                        ['id' => 'airteltigo','label' => 'AirtelTigo Money',    'sub' => 'Confirm via USSD or the app',                  'tag' => 'Money', 'tagColor' => 'bg-blue-100 text-blue-800'],
                        ['id' => 'card',      'label' => 'Debit / Credit Card', 'sub' => 'Visa, Mastercard — encrypted and secure',      'tag' => 'Card',  'tagColor' => 'bg-gray-100 text-gray-700'],
                    ] as $method)
                    <button
                        wire:click="$set('paymentMethod', '{{ $method['id'] }}')"
                        class="w-full text-left flex items-start gap-3 p-4 rounded-xl border-2 transition-colors cursor-pointer
                               {{ $paymentMethod === $method['id'] ? 'border-brand-red bg-brand-light-red/30' : 'border-brand-border hover:border-brand-border' }}">
                        <div class="w-5 h-5 rounded-full border-2 mt-0.5 shrink-0 flex items-center justify-center
                                    {{ $paymentMethod === $method['id'] ? 'border-brand-red' : 'border-brand-border' }}">
                            @if($paymentMethod === $method['id'])
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-red"></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-bold text-brand-text">{{ $method['label'] }}</span>
                                <span class="inline-flex items-center h-5 px-2 rounded-full text-[10px] font-bold {{ $method['tagColor'] }}">{{ $method['tag'] }}</span>
                            </div>
                            @if($paymentMethod === $method['id'])
                            <p class="text-[12px] text-brand-secondary-text mt-0.5">{{ $method['sub'] }}</p>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>

                {{-- Mobile-only: Promo code --}}
                <div class="md:hidden mt-6 pt-5 border-t border-brand-border-light">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Promo / Coupon Code</p>
                    @if($appliedCoupon)
                    <div class="flex items-center justify-between bg-brand-light-red rounded-xl px-4 py-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-red shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 14l2 2 4-4m-7 2A9 9 0 1 0 12 3a9 9 0 0 0-9 9h0z"/></svg>
                            <span class="text-sm font-bold text-brand-dark-red tracking-wide">{{ $appliedCoupon }}</span>
                            <span class="text-sm text-brand-success font-semibold">−GH₵ {{ number_format($discount, 2) }}</span>
                        </div>
                        <button wire:click="removeCoupon" class="text-[12px] text-brand-muted hover:text-brand-danger font-medium underline">Remove</button>
                    </div>
                    @else
                    <div class="flex gap-2">
                        <input
                            type="text"
                            wire:model="couponCode"
                            placeholder="Enter code"
                            class="flex-1 h-10 px-3 rounded-[10px] border border-brand-input-border text-sm focus:border-brand-red focus:ring-[2px] focus:ring-brand-light-red focus:outline-none uppercase"
                        >
                        <button
                            wire:click="applyCoupon"
                            class="h-10 px-4 rounded-[10px] bg-brand-text text-white text-sm font-semibold hover:opacity-90 cursor-pointer whitespace-nowrap">
                            Apply
                        </button>
                    </div>
                    @if($couponError)
                    <p class="text-xs text-brand-danger mt-1.5">{{ $couponError }}</p>
                    @endif
                    @endif
                </div>
            </div>

        </div>{{-- end left --}}

        {{-- ════════════ RIGHT — Order summary ════════════ --}}
        <div class="hidden md:block self-start sticky top-24">
            <div class="bg-white rounded-[18px] border border-brand-border-light p-6">
                <h2 class="text-[17px] font-extrabold mb-4">Order Summary</h2>

                {{-- Items --}}
                <div class="space-y-2.5 mb-4">
                    @foreach($orderItems as $item)
                    <div class="flex items-center justify-between gap-3 text-[13px]">
                        <span class="text-brand-secondary-text truncate">{{ $item['qty'] }} × {{ $item['name'] }}</span>
                        <span class="font-semibold shrink-0">GH₵ {{ number_format($item['price'] * $item['qty'], 2) }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Coupon code input --}}
                <div class="border-t border-brand-border-light pt-3 mb-3">
                    @if($appliedCoupon)
                    <div class="flex items-center justify-between bg-brand-light-red rounded-xl px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-red shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 14l2 2 4-4m-7 2A9 9 0 1 0 12 3a9 9 0 0 0-9 9h0z"/></svg>
                            <span class="text-sm font-bold text-brand-dark-red tracking-wide">{{ $appliedCoupon }}</span>
                        </div>
                        <button wire:click="removeCoupon" class="text-[12px] text-brand-muted hover:text-brand-danger font-medium underline">Remove</button>
                    </div>
                    @else
                    <div class="flex gap-2">
                        <input
                            type="text"
                            wire:model="couponCode"
                            placeholder="Promo code"
                            class="flex-1 h-9 px-3 rounded-[9px] border border-brand-input-border text-sm focus:border-brand-red focus:ring-[2px] focus:ring-brand-light-red focus:outline-none uppercase"
                        >
                        <button
                            wire:click="applyCoupon"
                            class="h-9 px-3 rounded-[9px] bg-brand-text text-white text-sm font-semibold hover:opacity-90 cursor-pointer whitespace-nowrap">
                            Apply
                        </button>
                    </div>
                    @if($couponError)
                    <p class="text-xs text-brand-danger mt-1.5">{{ $couponError }}</p>
                    @endif
                    @endif
                </div>

                {{-- Totals --}}
                <div class="border-t border-brand-border-light pt-3 space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-brand-secondary-text">Subtotal</span>
                        <span class="font-semibold">GH₵ {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-brand-secondary-text">
                            Delivery
                            @if($selectedZone)
                            · <span class="text-brand-text font-medium">{{ $selectedZone->name }}</span>
                            @else
                            <span class="text-brand-muted italic">(select area above)</span>
                            @endif
                        </span>
                        @if(! $zoneId)
                        <span class="text-brand-muted">—</span>
                        @elseif($deliveryFee == 0)
                        <span class="font-semibold text-brand-success">Free</span>
                        @else
                        <span class="font-semibold">GH₵ {{ number_format($deliveryFee, 2) }}</span>
                        @endif
                    </div>
                    @if($discount > 0)
                    <div class="flex justify-between text-brand-success">
                        <span>Discount · {{ $appliedCoupon }}</span>
                        <span class="font-semibold">−GH₵ {{ number_format($discount, 2) }}</span>
                    </div>
                    @endif
                </div>

                <div class="border-t border-brand-border-light my-4 pt-4 flex justify-between items-center">
                    <span class="font-extrabold text-base">Total</span>
                    <span class="text-[24px] font-extrabold">GH₵ {{ number_format($total, 2) }}</span>
                </div>

                {{-- Place Order CTA --}}
                <button
                    wire:click="placeOrder"
                    wire:loading.attr="disabled"
                    wire:target="placeOrder"
                    class="w-full h-[50px] flex items-center justify-center gap-2 rounded-full bg-brand-red hover:bg-brand-dark-red text-white font-semibold text-sm transition-colors cursor-pointer disabled:opacity-45">
                    <svg wire:loading wire:target="placeOrder" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span wire:loading.remove wire:target="placeOrder">Place Order · GH₵ {{ number_format($total, 2) }}</span>
                    <span wire:loading wire:target="placeOrder">Placing order…</span>
                </button>

                <p class="text-[11px] text-brand-muted text-center mt-3 leading-relaxed">
                    By placing your order you agree to our
                    <a href="{{ route('privacy') }}" class="underline">Terms</a> &amp;
                    <a href="{{ route('privacy') }}" class="underline">Privacy Policy</a>.
                    @if(in_array($paymentMethod, ['mtn', 'vodafone', 'airteltigo']))
                    A MoMo prompt will be sent to {{ $phone ?: 'your phone number' }}.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ MOBILE STICKY PLACE ORDER FOOTER ═══════════ --}}
<div class="md:hidden fixed bottom-[60px] inset-x-0 z-40 bg-white border-t border-brand-border-light px-4 py-3">
    <div class="flex items-center justify-between mb-2.5">
        <div>
            <span class="text-sm text-brand-secondary-text">Total</span>
            @if(! $zoneId)
            <p class="text-[11px] text-brand-muted">Select delivery area</p>
            @endif
        </div>
        <span class="text-xl font-extrabold">GH₵ {{ number_format($total, 2) }}</span>
    </div>
    <button
        wire:click="placeOrder"
        wire:loading.attr="disabled"
        wire:target="placeOrder"
        class="w-full h-12 flex items-center justify-center gap-2 rounded-full bg-brand-red active:bg-brand-dark-red text-white font-semibold text-sm transition-colors cursor-pointer disabled:opacity-45">
        <svg wire:loading wire:target="placeOrder" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        <span wire:loading.remove wire:target="placeOrder">Place Order · GH₵ {{ number_format($total, 2) }}</span>
        <span wire:loading wire:target="placeOrder">Placing order…</span>
    </button>
</div>

{{-- ═══════════ PAYSTACK INLINE POPUP ═══════════ --}}
<div
    x-data="{
        paystackKey: '{{ \App\Models\Setting::get('paystack_public_key') ?: config('paystack.public_key') }}',
        verifyUrl:   '{{ route('paystack.verify') }}',

        openPopup(data) {
            let handler = PaystackPop.setup({
                key:      this.paystackKey,
                email:    data.email,
                amount:   data.amount,
                currency: data.currency,
                ref:      data.ref,
                metadata: {
                    custom_fields: [
                        { display_name: 'Customer Name', variable_name: 'customer_name', value: data.name  },
                        { display_name: 'Phone',         variable_name: 'phone',         value: data.phone }
                    ]
                },
                callback: (response) => {
                    this.verifyPayment(response.reference);
                },
                onClose: () => {
                    $wire.set('placing', false);
                }
            });
            handler.openIframe();
        },

        async verifyPayment(reference) {
            try {
                const res  = await fetch(this.verifyUrl, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ reference })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    window.location.href = json.redirect;
                } else {
                    alert('Payment verification failed: ' + (json.message || 'Please contact support.'));
                    $wire.set('placing', false);
                }
            } catch (e) {
                alert('Network error during payment verification. Please contact support.');
                $wire.set('placing', false);
            }
        }
    }"
    x-on:fenroy:paystack.window="openPopup($event.detail)"
></div>
</div>{{-- single Livewire root --}}
