<div class="pos-grid"
    x-data
    x-init="window.addEventListener('sale-complete', e => window.open('/orders/receipt/'+e.detail.orderNumber+'?print=1','_blank'))"
>

{{-- ════════════════════════════════ HEADER (spans all 3 cols) ════════════════════════════════ --}}
<header class="pos-header">

    {{-- Logo zone — same width as sidebar --}}
    <div class="hdr-logo">
        <div style="width:38px;height:38px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
            </svg>
        </div>
        <div>
            <p style="color:#fff;font-size:17px;font-weight:900;margin:0;letter-spacing:-.5px;line-height:1.15;">Fenroy</p>
            <p style="color:rgba(255,255,255,.55);font-size:10px;margin:0;font-weight:500;letter-spacing:.04em;">POS System</p>
        </div>
    </div>

    {{-- Search bar --}}
    <div style="flex:1;padding:0 24px;position:relative;">
        <svg style="position:absolute;left:36px;top:50%;transform:translateY(-50%);width:20px;height:20px;color:#aaa;pointer-events:none;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Search products, scan barcode or SKU…"
            class="pos-search"
        >
        @if($search)
        <button wire:click="$set('search','')" type="button" style="position:absolute;right:28px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;font-size:22px;line-height:1;padding:4px;">×</button>
        @endif
    </div>

    {{-- Right: time + notification bell + user --}}
    <div style="min-width:220px;height:100%;display:flex;align-items:center;justify-content:flex-end;padding:0 20px;gap:14px;border-left:1px solid var(--fen-border);">
        <span style="font-size:13px;color:var(--fen-muted);font-weight:500;white-space:nowrap;"
            x-data x-text="new Date().toLocaleTimeString('en-GH',{hour:'2-digit',minute:'2-digit'})"
            x-init="setInterval(()=>$el.textContent=new Date().toLocaleTimeString('en-GH',{hour:'2-digit',minute:'2-digit'}),10000)">
        </span>
        @livewire('pos.notification-bell')
        <div style="display:flex;align-items:center;gap:9px;flex-shrink:0;">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--fen-red);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;font-weight:800;flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p style="font-size:13px;font-weight:700;color:var(--fen-text);margin:0;line-height:1.2;white-space:nowrap;">{{ auth()->user()->name }}</p>
                <p style="font-size:11px;color:var(--fen-muted);margin:0;">{{ auth()->user()->is_admin ? 'Administrator' : 'Sales Staff' }}</p>
            </div>
        </div>
    </div>
</header>

{{-- ════════════════════════════════ SIDEBAR (188px) ════════════════════════════════ --}}
<aside class="pos-sidebar">
    @php
    $sideNav = [
        ['key'=>'pos',       'label'=>'POS Terminal','href'=>'/pos',          'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['key'=>'products',  'label'=>'Products',    'href'=>'/pos/products', 'icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['key'=>'orders',    'label'=>'Orders',      'href'=>'/pos/orders',   'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['key'=>'customers', 'label'=>'Customers',   'href'=>'/pos/customers','icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
        ['key'=>'reports',   'label'=>'Reports',     'href'=>'/pos/reports',  'icon'=>'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['key'=>'discounts', 'label'=>'Discounts',   'href'=>'#', 'disabled'=>true, 'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ['key'=>'settings',  'label'=>'Settings',    'href'=>'/pos/settings', 'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
    @endphp

    <div style="flex:1;overflow-y:auto;">
        @foreach($sideNav as $item)
        @if($item['disabled'] ?? false)
        <div class="nav-item nav-disabled">
            <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
            {{ $item['label'] }}
        </div>
        @else
        <a href="{{ $item['href'] }}" class="nav-item {{ $item['key']==='pos' ? 'nav-active' : '' }}">
            <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
            {{ $item['label'] }}
        </a>
        @endif
        @endforeach
    </div>

    <div style="border-top:1px solid rgba(255,255,255,.15);padding-top:12px;">
        <form method="POST" action="/pos/logout">
            @csrf
            <button type="submit" class="nav-item">
                <svg style="width:18px;height:18px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </button>
        </form>
    </div>
</aside>

{{-- ════════════════════════════════ PRODUCT AREA (flexible) ════════════════════════════════ --}}
<main class="pos-main">

    {{-- Category bar --}}
    <div style="background:var(--fen-white);border-bottom:1px solid var(--fen-border);height:64px;display:flex;align-items:center;padding:0 20px;gap:8px;overflow-x:auto;flex-shrink:0;">
        <button wire:click="$set('activeCategory','')" type="button"
            class="cat-btn {{ $activeCategory==='' ? 'cat-active' : '' }}">All Items</button>
        @foreach($this->categories as $cat)
        <button wire:click="setCategory('{{ $cat }}')" type="button"
            class="cat-btn {{ $activeCategory===$cat ? 'cat-active' : '' }}">{{ $cat }}</button>
        @endforeach
    </div>

    {{-- Scrollable product grid --}}
    <div style="flex:1;overflow-y:auto;padding:16px 20px;min-height:0;">
        <div class="product-grid">
            @forelse($this->products as $product)
            @php $outOfStock = $product->stock !== null && $product->stock <= 0; @endphp
            <button
                wire:click="{{ $outOfStock ? '' : "addToCart('{$product->slug}')" }}"
                type="button"
                class="product-card"
                {{ $outOfStock ? 'disabled' : '' }}
            >
                <div style="width:100%;aspect-ratio:1/1;background:#f5f5f5;overflow:hidden;">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                         style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">
                </div>
                <div style="padding:8px 10px;">
                    <p style="font-size:12px;font-weight:700;color:var(--fen-text);line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:2px;">{{ $product->name }}</p>
                    @if($product->unit)
                    <p style="font-size:11px;color:var(--fen-muted);margin-bottom:3px;">{{ $product->unit }}</p>
                    @endif
                    @if($outOfStock)
                    <p style="font-size:11px;font-weight:700;color:var(--fen-danger);">Out of stock</p>
                    @else
                    <p style="font-size:13px;font-weight:800;color:var(--fen-red);margin-bottom:1px;">GH₵ {{ number_format($product->price, 2) }}</p>
                    @if($product->stock !== null)
                    <p style="font-size:10px;color:{{ $product->stock <= 10 ? '#d97706' : 'var(--fen-muted)' }};">Stock: {{ $product->stock }}</p>
                    @endif
                    @endif
                </div>
            </button>
            @empty
            <div style="grid-column:1/-1;text-align:center;padding:56px 20px;">
                <svg style="width:44px;height:44px;margin:0 auto 14px;display:block;color:var(--fen-border);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                <p style="font-size:15px;font-weight:700;color:var(--fen-text);margin-bottom:6px;">No products found</p>
                <p style="font-size:13px;color:var(--fen-muted);margin-bottom:14px;">Try another name, SKU or barcode.</p>
                @if($search)
                <button wire:click="$set('search','')" type="button" style="padding:8px 18px;background:var(--fen-red);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Clear Search</button>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination bar — pinned to bottom --}}
    <div style="background:var(--fen-white);border-top:1px solid var(--fen-border);height:64px;display:flex;align-items:center;justify-content:space-between;padding:0 20px;flex-shrink:0;">
        <span style="font-size:13px;color:var(--fen-muted);">
            @if($this->totalProducts > 0)
            Showing {{ min(($page-1)*$perPage+1, $this->totalProducts) }}–{{ min($page*$perPage, $this->totalProducts) }} of {{ $this->totalProducts }}
            @else
            No products
            @endif
        </span>
        @if($this->totalPages > 1)
        <div style="display:flex;align-items:center;gap:6px;">
            <button wire:click="prevPage" type="button" class="page-btn" {{ $page <= 1 ? 'disabled' : '' }}>‹</button>
            @for($p = max(1, $page-2); $p <= min($this->totalPages, $page+2); $p++)
            <button wire:click="goToPage({{ $p }})" type="button" class="page-btn {{ $page===$p ? 'page-active' : '' }}">{{ $p }}</button>
            @endfor
            <button wire:click="nextPage" type="button" class="page-btn" {{ $page >= $this->totalPages ? 'disabled' : '' }}>›</button>
        </div>
        @endif
    </div>
</main>

{{-- ════════════════════════════════ CART PANEL (420px) ════════════════════════════════ --}}
<aside class="pos-cart">

    {{-- Customer --}}
    <div style="padding:16px;border-bottom:1px solid var(--fen-border);flex-shrink:0;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <span style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--fen-muted);">Customer</span>
            <button type="button" style="padding:6px 14px;background:var(--fen-red);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">+ New Customer</button>
        </div>
        <input wire:model.blur="customerName" type="text" placeholder="Walk-in Customer" class="pos-input" style="margin-bottom:8px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <input wire:model.blur="customerPhone" type="tel" placeholder="Phone" class="pos-input">
            <input wire:model.blur="customerEmail" type="email" placeholder="Email" class="pos-input">
        </div>
    </div>

    {{-- Cart header --}}
    <div style="padding:12px 16px 8px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <span style="font-size:14px;font-weight:800;color:var(--fen-text);">
            Cart
            @if(count($cart) > 0)
            <span style="font-size:13px;color:var(--fen-muted);font-weight:400;">({{ array_sum(array_column($cart,'qty')) }} items)</span>
            @endif
        </span>
        @if(count($cart) > 0)
        <button wire:click="clearCart" type="button" style="font-size:12px;color:var(--fen-danger);background:none;border:none;cursor:pointer;font-weight:600;">Clear Cart</button>
        @endif
    </div>

    {{-- Cart items --}}
    <div style="flex:1;overflow-y:auto;min-height:0;">
        @forelse($cart as $item)
        <div class="cart-item">
            <div style="width:48px;height:48px;border-radius:8px;overflow:hidden;background:#f5f5f5;flex-shrink:0;">
                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:13px;font-weight:600;color:var(--fen-text);margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item['name'] }}</p>
                <p style="font-size:11px;color:var(--fen-muted);margin:2px 0 6px;">GH₵ {{ number_format($item['price'], 2) }}{{ $item['unit'] ? ' · '.$item['unit'] : '' }}</p>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button wire:click="updateQty('{{ $item['slug'] }}',{{ $item['qty']-1 }})" type="button" class="qty-btn">−</button>
                    <span style="font-size:14px;font-weight:700;color:var(--fen-text);min-width:22px;text-align:center;">{{ $item['qty'] }}</span>
                    <button wire:click="updateQty('{{ $item['slug'] }}',{{ $item['qty']+1 }})" type="button" class="qty-btn">+</button>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;justify-content:space-between;height:100%;gap:6px;flex-shrink:0;padding:2px 0;">
                <span style="font-size:14px;font-weight:800;color:var(--fen-red);">GH₵ {{ number_format($item['price']*$item['qty'], 2) }}</span>
                <button wire:click="removeFromCart('{{ $item['slug'] }}')" type="button"
                    style="border:none;background:none;cursor:pointer;color:#ccc;display:flex;align-items:center;justify-content:center;padding:2px;"
                    onmouseenter="this.style.color='var(--fen-danger)'" onmouseleave="this.style.color='#ccc'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:44px 24px;color:var(--fen-muted);">
            <svg style="width:44px;height:44px;margin:0 auto 14px;display:block;color:var(--fen-border);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
            <p style="font-size:14px;font-weight:600;color:var(--fen-text);margin:0 0 6px;">Cart is empty</p>
            <p style="font-size:13px;margin:0;">Tap a product to add it to the cart.</p>
        </div>
        @endforelse
    </div>

    {{-- Checkout --}}
    <div style="border-top:2px solid var(--fen-border);padding:16px;flex-shrink:0;">
        @php
            $subtotal = array_sum(array_map(fn($i)=>$i['price']*$i['qty'], $cart));
            $discount = max(0, min((float)$manualDiscount, $subtotal));
            $total    = max(0, $subtotal - $discount);
            $change   = max(0, (float)$amountTendered - $total);
        @endphp

        {{-- Discount --}}
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <span style="font-size:12px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;flex-shrink:0;">Discount</span>
            <input wire:model.live="manualDiscount" type="number" min="0" step="0.01" placeholder="0.00" class="pos-input" style="flex:1;text-align:right;">
            <span style="font-size:12px;font-weight:700;color:var(--fen-muted);flex-shrink:0;">GH₵</span>
        </div>

        {{-- Payment method --}}
        <div style="display:flex;gap:8px;margin-bottom:14px;">
            @foreach(['cash'=>'Cash','momo'=>'MoMo','card'=>'Card'] as $key=>$label)
            <button wire:click="$set('paymentMethod','{{ $key }}')" type="button"
                class="pay-method {{ $paymentMethod===$key ? 'pay-on' : '' }}">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Cash tendered + change --}}
        @if($paymentMethod === 'cash')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
            <div>
                <span style="display:block;font-size:10px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;">Amount Received</span>
                <input wire:model.live="amountTendered" type="number" min="0" step="0.01" placeholder="0.00" class="pos-input">
            </div>
            <div>
                <span style="display:block;font-size:10px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px;">Change</span>
                <div style="height:40px;border:1.5px solid;border-radius:8px;display:flex;align-items:center;padding:0 12px;font-size:13px;font-weight:700;
                    {{ $change>0 ? 'color:var(--fen-success);background:#f0fdf4;border-color:#bbf7d0;' : 'color:var(--fen-muted);background:var(--fen-bg);border-color:var(--fen-border);' }}">
                    GH₵ {{ number_format($change, 2) }}
                </div>
            </div>
        </div>
        @endif

        {{-- Summary --}}
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--fen-muted);margin-bottom:5px;">
                <span>Subtotal</span><span>GH₵ {{ number_format($subtotal, 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:{{ $discount>0?'var(--fen-success)':'var(--fen-muted)' }};margin-bottom:10px;">
                <span>Discount</span><span>{{ $discount>0?'−':'' }}GH₵ {{ number_format($discount, 2) }}</span>
            </div>
            <div style="border-top:2px solid var(--fen-border);padding-top:10px;display:flex;justify-content:space-between;align-items:baseline;">
                <span style="font-size:15px;font-weight:700;color:var(--fen-text);">TOTAL</span>
                <span style="font-size:28px;font-weight:800;color:var(--fen-red);line-height:1;">GH₵ {{ number_format($total, 2) }}</span>
            </div>
        </div>

        {{-- Note --}}
        <textarea wire:model.blur="note" placeholder="Note (optional)…"
            style="width:100%;border:1.5px solid var(--fen-border);border-radius:8px;padding:10px 12px;font-size:13px;color:var(--fen-text);outline:none;resize:none;height:52px;margin-bottom:12px;font-family:inherit;transition:border-color .15s;"
            onfocus="this.style.borderColor='var(--fen-red)'" onblur="this.style.borderColor='var(--fen-border)'"></textarea>

        {{-- Pay button --}}
        <button
            wire:click="completeSale"
            wire:loading.attr="disabled"
            wire:target="completeSale"
            type="button"
            class="pay-btn"
            {{ count($cart)===0 ? 'disabled' : '' }}
        >
            <svg wire:loading wire:target="completeSale" style="width:18px;height:18px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span wire:loading.remove wire:target="completeSale">PAY  GH₵ {{ number_format($total, 2) }}  →</span>
            <span wire:loading wire:target="completeSale">Processing…</span>
        </button>
    </div>
</aside>

</div>
