<div wire:poll.10s="refresh" style="position:relative;">

    {{-- Bell button --}}
    <button wire:click="toggle" type="button"
        style="position:relative;width:40px;height:40px;border-radius:10px;border:1.5px solid {{ $open ? 'var(--fen-red)' : 'var(--fen-border)' }};background:var(--fen-white);cursor:pointer;display:flex;align-items:center;justify-content:center;color:{{ $open ? 'var(--fen-red)' : 'var(--fen-muted)' }};flex-shrink:0;transition:border-color .15s,color .15s;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($count > 0)
        <span style="position:absolute;top:-5px;right:-5px;min-width:18px;height:18px;background:var(--fen-red);color:#fff;font-size:10px;font-weight:800;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 4px;border:2px solid #fff;line-height:1;">{{ $count > 9 ? '9+' : $count }}</span>
        @endif
    </button>

    {{-- Dropdown (pure Livewire, no Alpine) --}}
    @if($open)
    <div style="position:absolute;top:calc(100% + 8px);right:0;width:320px;background:#fff;border:1px solid var(--fen-border);border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.13);z-index:200;overflow:hidden;">

        <div style="padding:14px 16px;border-bottom:1px solid var(--fen-border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <p style="font-size:14px;font-weight:800;color:var(--fen-text);margin:0;">Online Orders</p>
                <p style="font-size:12px;color:var(--fen-muted);margin:0;">Awaiting acceptance</p>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                @if($count > 0)
                <span style="background:var(--fen-light);color:var(--fen-dark);font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;">{{ $count }} new</span>
                @endif
                <button wire:click="toggle" type="button" style="background:none;border:none;cursor:pointer;color:var(--fen-muted);font-size:18px;line-height:1;padding:2px 6px;">×</button>
            </div>
        </div>

        @if($recent->isEmpty())
        <div style="padding:32px 16px;text-align:center;">
            <svg style="width:36px;height:36px;color:var(--fen-border);margin:0 auto 10px;display:block;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p style="font-size:13px;color:var(--fen-muted);margin:0;">No pending online orders</p>
        </div>
        @else
        <div style="max-height:280px;overflow-y:auto;">
            @foreach($recent as $order)
            <div style="padding:12px 16px;border-bottom:1px solid #fafafa;display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--fen-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="var(--fen-red)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;font-weight:700;color:var(--fen-text);margin:0;">#{{ $order->order_number }}</p>
                    <p style="font-size:12px;color:var(--fen-muted);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $order->customer_name ?? 'Online Customer' }}</p>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <p style="font-size:13px;font-weight:800;color:var(--fen-red);margin:0;">GH₵ {{ number_format($order->total, 2) }}</p>
                    <p style="font-size:10px;color:var(--fen-muted);margin:0;">{{ $order->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div style="padding:12px 16px;">
            <a href="{{ route('pos.orders') }}?tab=online" wire:navigate style="display:block;text-align:center;padding:10px;background:var(--fen-red);color:#fff;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;">
                View All Online Orders →
            </a>
        </div>
        @endif
    </div>
    @endif
</div>
