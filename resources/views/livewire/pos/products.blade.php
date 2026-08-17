<div class="pos-grid-2">

    {{-- HEADER --}}
    <header class="pos-header">
        <div class="hdr-logo">
            <div style="width:38px;height:38px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <div>
                <p style="color:#fff;font-size:17px;font-weight:900;margin:0;letter-spacing:-.5px;line-height:1.15;">Fenroy</p>
                <p style="color:rgba(255,255,255,.55);font-size:10px;margin:0;font-weight:500;">POS System</p>
            </div>
        </div>
        <div style="flex:1;padding:0 28px;">
            <h1 style="font-size:17px;font-weight:800;color:var(--fen-text);margin:0;">Products</h1>
            <p style="font-size:12px;color:var(--fen-muted);margin:0;">View-only — edit from admin panel</p>
        </div>
        <div style="padding:0 20px;display:flex;align-items:center;gap:12px;">
            <div style="position:relative;">
                <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#aaa;pointer-events:none;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search…"
                    style="height:40px;padding:0 12px 0 34px;border:1.5px solid var(--fen-border);border-radius:8px;font-size:13px;background:var(--fen-bg);outline:none;width:200px;" onfocus="this.style.borderColor='var(--fen-red)'" onblur="this.style.borderColor='var(--fen-border)'">
            </div>
            @livewire('pos.notification-bell')
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <div style="width:36px;height:36px;border-radius:50%;background:var(--fen-red);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                <div><p style="font-size:13px;font-weight:700;color:var(--fen-text);margin:0;white-space:nowrap;">{{ auth()->user()->name }}</p><p style="font-size:11px;color:var(--fen-muted);margin:0;">{{ auth()->user()->is_admin?'Admin':'Staff' }}</p></div>
            </div>
        </div>
    </header>

    {{-- SIDEBAR --}}
    @include('livewire.pos.partials.sidebar', ['activePage' => 'products'])

    {{-- MAIN --}}
    <main class="pos-main">
        {{-- Category bar --}}
        <div style="background:var(--fen-white);border-bottom:1px solid var(--fen-border);height:60px;display:flex;align-items:center;padding:0 24px;gap:8px;overflow-x:auto;flex-shrink:0;">
            <button wire:click="$set('activeCategory','')" type="button" class="cat-btn {{ $activeCategory===''?'cat-active':'' }}">All</button>
            @foreach($this->categories as $cat)
            <button wire:click="setCategory('{{ $cat }}')" type="button" class="cat-btn {{ $activeCategory===$cat?'cat-active':'' }}">{{ $cat }}</button>
            @endforeach
        </div>

        <div style="flex:1;overflow-y:auto;padding:24px;min-height:0;">
            <div style="background:var(--fen-white);border:1px solid var(--fen-border);border-radius:12px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--fen-bg);">
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Product</th>
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Category</th>
                            <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Price</th>
                            <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Stock</th>
                            <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr style="border-top:1px solid var(--fen-border);">
                            <td style="padding:12px 16px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div style="width:44px;height:44px;border-radius:8px;overflow:hidden;background:#f5f5f5;flex-shrink:0;">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                                    </div>
                                    <div>
                                        <p style="font-size:13px;font-weight:700;color:var(--fen-text);margin:0;">{{ $product->name }}</p>
                                        @if($product->unit)<p style="font-size:11px;color:var(--fen-muted);margin:0;">{{ $product->unit }}</p>@endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 16px;font-size:13px;color:var(--fen-muted);">{{ $product->category ?? '—' }}</td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:var(--fen-red);">GH₵ {{ number_format($product->price,2) }}</td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:{{ ($product->stock!==null&&$product->stock<=10)?'#d97706':'var(--fen-text)' }};">{{ $product->stock??'∞' }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                @if($product->stock!==null&&$product->stock<=0)
                                <span style="padding:3px 10px;background:#fef2f2;color:var(--fen-danger);font-size:11px;font-weight:700;border-radius:20px;">Out of stock</span>
                                @elseif($product->stock!==null&&$product->stock<=10)
                                <span style="padding:3px 10px;background:#fffbeb;color:#92400e;font-size:11px;font-weight:700;border-radius:20px;">Low stock</span>
                                @else
                                <span style="padding:3px 10px;background:#f0fdf4;color:var(--fen-ok);font-size:11px;font-weight:700;border-radius:20px;">In stock</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="padding:48px;text-align:center;color:var(--fen-muted);font-size:14px;">No products found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:13px;color:var(--fen-muted);">{{ $products->total() }} products total</span>
                <div style="font-size:13px;">{{ $products->links() }}</div>
            </div>
        </div>
    </main>
</div>
