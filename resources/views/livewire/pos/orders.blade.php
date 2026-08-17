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
            <h1 style="font-size:17px;font-weight:800;color:var(--fen-text);margin:0;">Orders</h1>
            <p style="font-size:12px;color:var(--fen-muted);margin:0;">Walk-in &amp; online orders</p>
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
    @include('livewire.pos.partials.sidebar', ['activePage' => 'orders'])

    {{-- MAIN --}}
    <main class="pos-main">
        {{-- Tab bar + filter --}}
        <div style="background:var(--fen-white);border-bottom:1px solid var(--fen-border);height:60px;display:flex;align-items:center;padding:0 24px;gap:16px;flex-shrink:0;">
            <button wire:click="$set('tab','walkin')" type="button"
                style="height:36px;padding:0 16px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:700;transition:all .12s;
                {{ $tab==='walkin' ? 'background:var(--fen-red);color:#fff;' : 'background:transparent;color:var(--fen-muted);' }}">
                Walk-in Sales
            </button>
            <button wire:click="$set('tab','online')" type="button"
                style="height:36px;padding:0 16px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:700;transition:all .12s;position:relative;
                {{ $tab==='online' ? 'background:var(--fen-red);color:#fff;' : 'background:transparent;color:var(--fen-muted);' }}">
                Online Orders
                @if($pendingOnlineCount > 0)
                <span style="position:absolute;top:-4px;right:-4px;min-width:18px;height:18px;background:{{ $tab==='online'?'#fff':'var(--fen-red)' }};color:{{ $tab==='online'?'var(--fen-red)':'#fff' }};font-size:10px;font-weight:800;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 4px;">{{ $pendingOnlineCount }}</span>
                @endif
            </button>
            <div style="flex:1;"></div>
            <select wire:model.live="dateFilter" style="height:36px;padding:0 12px;border:1.5px solid var(--fen-border);border-radius:8px;font-size:13px;background:var(--fen-bg);outline:none;cursor:pointer;">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="all">All Time</option>
            </select>
        </div>

        <div style="flex:1;overflow-y:auto;padding:24px;min-height:0;">

            @if($tab === 'online')
            {{-- Online orders that need attention --}}
            @if($pendingOnlineCount > 0)
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                <svg width="18" height="18" fill="none" stroke="#92400e" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <p style="font-size:13px;font-weight:700;color:#92400e;margin:0;">{{ $pendingOnlineCount }} online order{{ $pendingOnlineCount>1?'s':'' }} awaiting acceptance</p>
            </div>
            @endif
            @endif

            <div style="background:var(--fen-white);border:1px solid var(--fen-border);border-radius:12px;overflow:hidden;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--fen-bg);">
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Order #</th>
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Customer</th>
                            <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Items</th>
                            <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Payment</th>
                            <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Total</th>
                            <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Status</th>
                            <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Date</th>
                            <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr style="border-top:1px solid var(--fen-border);{{ $order->status==='pending'&&!$order->is_walk_in?'background:#fffbeb;':'' }}">
                            <td style="padding:12px 16px;">
                                <p style="font-size:13px;font-weight:700;color:var(--fen-text);margin:0;">#{{ $order->order_number }}</p>
                            </td>
                            <td style="padding:12px 16px;">
                                <p style="font-size:13px;font-weight:600;color:var(--fen-text);margin:0;">{{ $order->customer_name ?? 'Walk-in' }}</p>
                                @if($order->customer_phone)<p style="font-size:11px;color:var(--fen-muted);margin:0;">{{ $order->customer_phone }}</p>@endif
                            </td>
                            <td style="padding:12px 16px;text-align:center;font-size:13px;color:var(--fen-muted);">{{ count($order->items ?? []) }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                <span style="padding:3px 10px;background:var(--fen-bg);color:var(--fen-text);font-size:11px;font-weight:600;border-radius:20px;text-transform:capitalize;">{{ $order->payment_method ?? '—' }}</span>
                            </td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:var(--fen-red);">GH₵ {{ number_format($order->total,2) }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $statusColors = ['pending'=>['bg'=>'#fffbeb','text'=>'#92400e'],'processing'=>['bg'=>'#eff6ff','text'=>'#1e40af'],'completed'=>['bg'=>'#f0fdf4','text'=>'#166534'],'cancelled'=>['bg'=>'#fef2f2','text'=>'#991b1b']];
                                    $sc = $statusColors[$order->status] ?? ['bg'=>'var(--fen-bg)','text'=>'var(--fen-muted)'];
                                @endphp
                                <span style="padding:3px 10px;background:{{ $sc['bg'] }};color:{{ $sc['text'] }};font-size:11px;font-weight:700;border-radius:20px;text-transform:capitalize;">{{ $order->status }}</span>
                            </td>
                            <td style="padding:12px 16px;text-align:right;font-size:12px;color:var(--fen-muted);">{{ \Carbon\Carbon::parse($order->created_at)->format('d M, H:i') }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
                                    @if(!$order->is_walk_in && $order->status === 'pending')
                                    <button wire:click="acceptOrder({{ $order->id }})" type="button"
                                        style="padding:5px 12px;background:var(--fen-ok);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;">
                                        Accept
                                    </button>
                                    @endif
                                    <a href="/orders/receipt/{{ $order->order_number }}" target="_blank"
                                        style="padding:5px 12px;background:var(--fen-bg);color:var(--fen-text);border:1px solid var(--fen-border);border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                                        Receipt
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" style="padding:48px;text-align:center;color:var(--fen-muted);font-size:14px;">No orders found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:13px;color:var(--fen-muted);">Total revenue: <strong style="color:var(--fen-text);">GH₵ {{ number_format($totalRevenue,2) }}</strong></span>
                <div style="font-size:13px;">{{ $orders->links() }}</div>

            </div>
        </div>
    </main>
</div>
