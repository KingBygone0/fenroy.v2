<x-filament-panels::page>
<div
    x-data
    x-init="
        window.addEventListener('pos-sale-complete', e => {
            window.open('/orders/receipt/' + e.detail.orderNumber + '?print=1', '_blank');
        });
        window.addEventListener('pos-error', e => alert(e.detail.message));
    "
    style="margin: -1.5rem; height: calc(100vh - 4rem); display: flex; flex-direction: column;"
>

{{-- ── Top bar ─────────────────────────────────────────────────────── --}}
<div style="padding: 12px 16px; background:#fff; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:12px; flex-shrink:0;">
    <svg style="width:20px;height:20px;color:#C8102E;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
    </svg>
    <h1 style="font-size:16px;font-weight:700;color:#111;margin:0;">Point of Sale</h1>
    <span style="font-size:12px;color:#9ca3af;margin-left:4px;">— Walk-in sales terminal</span>
</div>

{{-- ── Main layout ──────────────────────────────────────────────────── --}}
<div style="flex:1; overflow:hidden; display:flex; gap:0;">

    {{-- ════════════ LEFT: Product browser ════════════ --}}
    <div style="flex:1; min-width:0; display:flex; flex-direction:column; background:#f9fafb; border-right:1px solid #e5e7eb;">

        {{-- Search --}}
        <div style="padding:12px; background:#fff; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <div style="position:relative;">
                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#9ca3af;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Search by name, SKU or category…"
                    style="width:100%;height:38px;padding:0 12px 0 34px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;outline:none;background:#fff;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#C8102E'" onblur="this.style.borderColor='#d1d5db'"
                >
            </div>
        </div>

        {{-- Product grid --}}
        <div style="flex:1;overflow-y:auto;padding:12px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;">
                @forelse($this->products as $product)
                @php $outOfStock = $product->stock !== null && $product->stock <= 0; @endphp
                <button
                    wire:click="{{ $outOfStock ? '' : 'addToCart(\'' . $product->slug . '\')' }}"
                    type="button"
                    style="
                        text-align:left;
                        padding:10px;
                        border-radius:10px;
                        border:1px solid {{ $outOfStock ? '#f3f4f6' : '#e5e7eb' }};
                        background:{{ $outOfStock ? '#f9fafb' : '#fff' }};
                        cursor:{{ $outOfStock ? 'not-allowed' : 'pointer' }};
                        opacity:{{ $outOfStock ? '0.5' : '1' }};
                        transition:border-color 0.15s,box-shadow 0.15s;
                        width:100%;
                    "
                    {{ $outOfStock ? 'disabled' : '' }}
                    @if(!$outOfStock)
                    onmouseenter="this.style.borderColor='#C8102E';this.style.boxShadow='0 0 0 3px rgba(200,16,46,0.08)'"
                    onmouseleave="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'"
                    @endif
                >
                    <div style="width:100%;aspect-ratio:1;border-radius:6px;overflow:hidden;background:#f3f4f6;margin-bottom:8px;">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                             style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                    </div>
                    <p style="font-size:12px;font-weight:600;color:#111;line-height:1.3;margin:0 0 2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $product->name }}</p>
                    @if($product->unit)
                    <p style="font-size:11px;color:#9ca3af;margin:0 0 4px;">{{ $product->unit }}</p>
                    @endif
                    <p style="font-size:13px;font-weight:700;color:#C8102E;margin:0;">GH₵ {{ number_format($product->price, 2) }}</p>
                    @if($product->stock !== null)
                    <p style="font-size:10px;color:{{ $product->stock <= 5 ? '#d97706' : '#9ca3af' }};margin:2px 0 0;">
                        {{ $outOfStock ? 'Out of stock' : 'Stock: ' . $product->stock }}
                    </p>
                    @endif
                </button>
                @empty
                <div style="grid-column:1/-1;text-align:center;padding:48px 0;color:#9ca3af;font-size:13px;">
                    {{ $search ? 'No products match "' . $search . '"' : 'No active products.' }}
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ════════════ RIGHT: Cart & checkout ════════════ --}}
    <div style="width:340px;flex-shrink:0;display:flex;flex-direction:column;background:#fff;overflow:hidden;">

        {{-- Customer info --}}
        <div style="padding:12px 14px;border-bottom:1px solid #f3f4f6;flex-shrink:0;">
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#9ca3af;margin:0 0 8px;">Customer</p>
            <input wire:model.blur="customerName" type="text" placeholder="Name (Walk-in Customer)"
                style="width:100%;height:34px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12px;margin-bottom:6px;outline:none;box-sizing:border-box;"
                onfocus="this.style.borderColor='#C8102E'" onblur="this.style.borderColor='#e5e7eb'">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                <input wire:model.blur="customerPhone" type="tel" placeholder="Phone"
                    style="width:100%;height:34px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12px;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#C8102E'" onblur="this.style.borderColor='#e5e7eb'">
                <input wire:model.blur="customerEmail" type="email" placeholder="Email"
                    style="width:100%;height:34px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12px;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#C8102E'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
        </div>

        {{-- Cart items --}}
        <div style="flex:1;overflow-y:auto;min-height:0;">
            <div style="padding:8px 14px 4px;display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#9ca3af;margin:0;">
                    Cart
                    @if(count($cart) > 0)
                    <span style="font-size:11px;font-weight:600;color:#6b7280;text-transform:none;letter-spacing:0;">({{ count($cart) }} {{ count($cart) === 1 ? 'item' : 'items' }})</span>
                    @endif
                </p>
                @if(count($cart) > 0)
                <button wire:click="clearCart" type="button"
                    style="font-size:11px;color:#ef4444;background:none;border:none;cursor:pointer;padding:0;font-weight:500;">
                    Clear all
                </button>
                @endif
            </div>

            @forelse($cart as $item)
            <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-bottom:1px solid #f9fafb;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:12px;font-weight:600;color:#111;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item['name'] }}</p>
                    <p style="font-size:11px;color:#9ca3af;margin:1px 0 0;">GH₵ {{ number_format($item['price'], 2) }} {{ $item['unit'] ? '/ ' . $item['unit'] : '' }}</p>
                </div>
                {{-- Qty stepper --}}
                <div style="display:flex;align-items:center;gap:4px;flex-shrink:0;">
                    <button wire:click="updateQty('{{ $item['slug'] }}', {{ $item['qty'] - 1 }})" type="button"
                        style="width:24px;height:24px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;font-size:14px;font-weight:700;color:#374151;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;">−</button>
                    <span style="font-size:13px;font-weight:700;color:#111;width:20px;text-align:center;">{{ $item['qty'] }}</span>
                    <button wire:click="updateQty('{{ $item['slug'] }}', {{ $item['qty'] + 1 }})" type="button"
                        style="width:24px;height:24px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;font-size:14px;font-weight:700;color:#374151;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;">+</button>
                </div>
                <span style="font-size:12px;font-weight:700;color:#111;width:60px;text-align:right;flex-shrink:0;">GH₵ {{ number_format($item['price'] * $item['qty'], 2) }}</span>
                <button wire:click="removeFromCart('{{ $item['slug'] }}')" type="button"
                    style="width:20px;height:20px;border:none;background:none;cursor:pointer;color:#d1d5db;padding:0;flex-shrink:0;display:flex;align-items:center;justify-content:center;"
                    onmouseenter="this.style.color='#ef4444'" onmouseleave="this.style.color='#d1d5db'">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            @empty
            <div style="text-align:center;padding:32px 16px;color:#d1d5db;font-size:12px;">
                <svg style="width:32px;height:32px;margin:0 auto 8px;display:block;opacity:0.4;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                Tap a product to add it
            </div>
            @endforelse
        </div>

        {{-- Payment & totals --}}
        <div style="border-top:2px solid #f3f4f6;padding:12px 14px;flex-shrink:0;background:#fff;">

            {{-- Payment method --}}
            <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#9ca3af;margin:0 0 8px;">Payment Method</p>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:12px;">
                @foreach(['cash' => 'Cash', 'momo' => 'MoMo', 'card' => 'Card'] as $key => $label)
                <button wire:click="$set('paymentMethod', '{{ $key }}')" type="button"
                    style="
                        height:34px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid;cursor:pointer;transition:all 0.15s;
                        {{ $paymentMethod === $key
                            ? 'background:#111;color:#fff;border-color:#111;'
                            : 'background:#fff;color:#6b7280;border-color:#e5e7eb;' }}
                    ">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            @php
                $subtotal = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $cart));
                $discount = max(0, min((float) $manualDiscount, $subtotal));
                $total    = max(0, $subtotal - $discount);
                $change   = max(0, (float) $amountTendered - $total);
            @endphp

            {{-- Cash: tendered + change --}}
            @if($paymentMethod === 'cash')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
                <div>
                    <label style="font-size:10px;font-weight:600;color:#9ca3af;display:block;margin-bottom:4px;">Amount received</label>
                    <input wire:model.live="amountTendered" type="number" min="0" step="0.01" placeholder="0.00"
                        style="width:100%;height:34px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#C8102E'" onblur="this.style.borderColor='#e5e7eb'">
                </div>
                <div>
                    <label style="font-size:10px;font-weight:600;color:#9ca3af;display:block;margin-bottom:4px;">Change</label>
                    <div style="height:34px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:13px;font-weight:700;display:flex;align-items:center;
                        {{ $change > 0 ? 'color:#16a34a;background:#f0fdf4;border-color:#bbf7d0;' : 'color:#9ca3af;background:#f9fafb;' }}">
                        GH₵ {{ number_format($change, 2) }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Discount --}}
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <label style="font-size:10px;font-weight:600;color:#9ca3af;white-space:nowrap;">Discount (GH₵)</label>
                <input wire:model.live="manualDiscount" type="number" min="0" step="0.01" placeholder="0.00"
                    style="flex:1;height:32px;padding:0 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12px;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#C8102E'" onblur="this.style.borderColor='#e5e7eb'">
            </div>

            {{-- Totals --}}
            <div style="background:#f9fafb;border-radius:10px;padding:10px 12px;margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:4px;">
                    <span>Subtotal</span><span>GH₵ {{ number_format($subtotal, 2) }}</span>
                </div>
                @if($discount > 0)
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#16a34a;margin-bottom:4px;">
                    <span>Discount</span><span>−GH₵ {{ number_format($discount, 2) }}</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;color:#111;border-top:1px solid #e5e7eb;padding-top:8px;margin-top:4px;">
                    <span>Total</span><span>GH₵ {{ number_format($total, 2) }}</span>
                </div>
            </div>

            {{-- Note --}}
            <textarea wire:model.blur="note" rows="2" placeholder="Note (optional)…"
                style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:7px;font-size:12px;margin-bottom:10px;resize:none;outline:none;box-sizing:border-box;font-family:inherit;"
                onfocus="this.style.borderColor='#C8102E'" onblur="this.style.borderColor='#e5e7eb'"></textarea>

            {{-- Complete Sale --}}
            <button
                wire:click="completeSale"
                wire:loading.attr="disabled"
                wire:target="completeSale"
                type="button"
                style="
                    width:100%;height:48px;border-radius:10px;font-size:14px;font-weight:700;
                    border:none;cursor:{{ count($cart) > 0 ? 'pointer' : 'not-allowed' }};
                    display:flex;align-items:center;justify-content:center;gap:8px;
                    transition:background 0.15s;
                    background:{{ count($cart) > 0 ? '#C8102E' : '#e5e7eb' }};
                    color:{{ count($cart) > 0 ? '#fff' : '#9ca3af' }};
                "
                {{ count($cart) === 0 ? 'disabled' : '' }}
            >
                <svg wire:loading wire:target="completeSale" style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading.remove wire:target="completeSale">
                    @if(count($cart) > 0)
                        Complete Sale · GH₵ {{ number_format($total, 2) }}
                    @else
                        Add items to cart
                    @endif
                </span>
                <span wire:loading wire:target="completeSale">Processing…</span>
            </button>

        </div>
    </div>

</div>
</div>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
</x-filament-panels::page>
