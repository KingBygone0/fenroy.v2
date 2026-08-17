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
            <h1 style="font-size:17px;font-weight:800;color:var(--fen-text);margin:0;">Reports</h1>
            <p style="font-size:12px;color:var(--fen-muted);margin:0;">Sales analytics</p>
        </div>
        <div style="padding:0 20px;display:flex;align-items:center;gap:12px;">
            <select wire:model.live="period" style="height:40px;padding:0 12px;border:1.5px solid var(--fen-border);border-radius:8px;font-size:13px;background:var(--fen-bg);outline:none;cursor:pointer;">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
            @livewire('pos.notification-bell')
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <div style="width:36px;height:36px;border-radius:50%;background:var(--fen-red);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:800;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                <div><p style="font-size:13px;font-weight:700;color:var(--fen-text);margin:0;white-space:nowrap;">{{ auth()->user()->name }}</p><p style="font-size:11px;color:var(--fen-muted);margin:0;">{{ auth()->user()->is_admin?'Admin':'Staff' }}</p></div>
            </div>
        </div>
    </header>

    {{-- SIDEBAR --}}
    @include('livewire.pos.partials.sidebar', ['activePage' => 'reports'])

    {{-- MAIN --}}
    <main class="pos-main">
        <div style="flex:1;overflow-y:auto;padding:24px;min-height:0;">

            {{-- Stat cards --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
                @php
                $cards = [
                    ['label'=>'Total Revenue',    'value'=>'GH₵ '.number_format($stats['total_revenue'],2),    'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label'=>'Total Orders',     'value'=>$stats['total_orders'],                               'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['label'=>'Walk-in Revenue',  'value'=>'GH₵ '.number_format($stats['walk_in_revenue'],2),  'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['label'=>'Online Revenue',   'value'=>'GH₵ '.number_format($stats['online_revenue'],2),   'icon'=>'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064'],
                ];
                @endphp
                @foreach($cards as $card)
                <div style="background:var(--fen-white);border:1px solid var(--fen-border);border-radius:12px;padding:20px 20px 16px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
                        <div style="width:40px;height:40px;background:var(--fen-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <svg width="20" height="20" fill="none" stroke="var(--fen-red)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                        </div>
                    </div>
                    <p style="font-size:22px;font-weight:800;color:var(--fen-text);margin:0 0 4px;">{{ $card['value'] }}</p>
                    <p style="font-size:12px;color:var(--fen-muted);margin:0;font-weight:600;">{{ $card['label'] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Top products --}}
            <div style="background:var(--fen-white);border:1px solid var(--fen-border);border-radius:12px;overflow:hidden;">
                <div style="padding:16px 20px;border-bottom:1px solid var(--fen-border);">
                    <h2 style="font-size:14px;font-weight:800;color:var(--fen-text);margin:0;">Top Products This Month</h2>
                </div>
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--fen-bg);">
                            <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">#</th>
                            <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Product</th>
                            <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Units Sold</th>
                            <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:var(--fen-muted);text-transform:uppercase;letter-spacing:.06em;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $i => $product)
                        <tr style="border-top:1px solid var(--fen-border);">
                            <td style="padding:12px 16px;font-size:13px;font-weight:700;color:{{ $i<3?'var(--fen-red)':'var(--fen-muted)' }};">{{ $i+1 }}</td>
                            <td style="padding:12px 16px;font-size:13px;font-weight:600;color:var(--fen-text);">{{ $product['name'] }}</td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;color:var(--fen-muted);">{{ $product['qty'] }}</td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:var(--fen-red);">GH₵ {{ number_format($product['revenue'],2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="padding:32px;text-align:center;color:var(--fen-muted);font-size:14px;">No sales data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>
