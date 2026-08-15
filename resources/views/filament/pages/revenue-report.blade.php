<style>
:root {
    --c-bg:#ffffff; --c-border:#e5e7eb; --c-text:#111827; --c-text2:#6b7280;
    --c-muted:#9ca3af; --c-surface:#f9fafb; --c-divider:#f3f4f6;
    --c-input-bg:#ffffff; --c-input-border:#d1d5db; --c-toggle:#f9fafb;
}
html.dark {
    --c-bg:#1f2937; --c-border:#374151; --c-text:#f9fafb; --c-text2:#d1d5db;
    --c-muted:#9ca3af; --c-surface:#111827; --c-divider:#374151;
    --c-input-bg:#111827; --c-input-border:#4b5563; --c-toggle:#111827;
}
</style>

@once
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @endpush
@endonce

<x-filament-panels::page>
<div style="display:flex;flex-direction:column;gap:20px;">

    {{-- ── Filter bar ── --}}
    <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:20px;display:flex;flex-wrap:wrap;gap:20px;align-items:flex-end;">
        <div>
            <p style="font-size:11px;font-weight:600;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px;">Date Range</p>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="date" wire:model.live="dateFrom"
                    style="height:36px;border:1px solid var(--c-input-border);border-radius:8px;padding:0 12px;font-size:13px;background:var(--c-input-bg);color:var(--c-text);outline:none;">
                <span style="font-size:13px;color:var(--c-muted);">to</span>
                <input type="date" wire:model.live="dateTo"
                    style="height:36px;border:1px solid var(--c-input-border);border-radius:8px;padding:0 12px;font-size:13px;background:var(--c-input-bg);color:var(--c-text);outline:none;">
            </div>
        </div>
        <div>
            <p style="font-size:11px;font-weight:600;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px;">Group By</p>
            <div style="display:inline-flex;border:1px solid var(--c-border);border-radius:8px;background:var(--c-surface);padding:3px;gap:2px;">
                @foreach(['day' => 'Day', 'week' => 'Week', 'month' => 'Month'] as $val => $label)
                <button wire:click="$set('groupBy','{{ $val }}')"
                    style="height:30px;padding:0 14px;border-radius:6px;font-size:13px;font-weight:500;border:none;cursor:pointer;transition:all .15s;
                        {{ $groupBy === $val
                            ? 'background:var(--c-bg);color:var(--c-text);box-shadow:0 1px 3px rgba(0,0,0,.15);'
                            : 'background:transparent;color:var(--c-text2);' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
        <div style="flex:1;"></div>
        <button wire:click="exportCsv" wire:loading.attr="disabled"
            style="display:inline-flex;align-items:center;gap:8px;height:36px;padding:0 16px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            <span wire:loading.remove wire:target="exportCsv">Export CSV</span>
            <span wire:loading wire:target="exportCsv">Exporting…</span>
        </button>
    </div>

    {{-- ── KPI cards ── --}}
    @php $totals = $this->getTotals(); @endphp
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">

        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-top:3px solid #3b82f6;border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span style="font-size:11px;font-weight:600;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;">Revenue</span>
                <span style="width:32px;height:32px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                </span>
            </div>
            <p style="font-size:22px;font-weight:800;color:var(--c-text);font-variant-numeric:tabular-nums;">GH₵ {{ number_format($totals['revenue'], 2) }}</p>
            @if($totals['prev_revenue'] > 0 || $totals['change'] != 0)
                <span style="display:inline-flex;align-items:center;gap:4px;margin-top:8px;font-size:11px;font-weight:600;padding:2px 8px;border-radius:999px;
                    {{ $totals['change'] >= 0 ? 'background:#f0fdf4;color:#16a34a;' : 'background:#fef2f2;color:#dc2626;' }}">
                    {{ $totals['change'] >= 0 ? '▲' : '▼' }} {{ abs($totals['change']) }}% vs prev
                </span>
            @else
                <p style="font-size:11px;color:var(--c-muted);margin-top:8px;">No prior period</p>
            @endif
        </div>

        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-top:3px solid #8b5cf6;border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span style="font-size:11px;font-weight:600;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;">Orders</span>
                <span style="width:32px;height:32px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/></svg>
                </span>
            </div>
            <p style="font-size:22px;font-weight:800;color:var(--c-text);font-variant-numeric:tabular-nums;">{{ number_format($totals['orders']) }}</p>
            <p style="font-size:11px;color:var(--c-muted);margin-top:8px;">paid in period</p>
        </div>

        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-top:3px solid #f59e0b;border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span style="font-size:11px;font-weight:600;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;">Avg Order</span>
                <span style="width:32px;height:32px;border-radius:8px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                </span>
            </div>
            <p style="font-size:22px;font-weight:800;color:var(--c-text);font-variant-numeric:tabular-nums;">GH₵ {{ number_format($totals['avg_order'], 2) }}</p>
            <p style="font-size:11px;color:var(--c-muted);margin-top:8px;">per paid order</p>
        </div>

        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-top:3px solid #10b981;border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span style="font-size:11px;font-weight:600;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;">Net Revenue</span>
                <span style="width:32px;height:32px;border-radius:8px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                </span>
            </div>
            <p style="font-size:22px;font-weight:800;color:var(--c-text);font-variant-numeric:tabular-nums;">GH₵ {{ number_format($totals['net_revenue'], 2) }}</p>
            <p style="font-size:11px;color:var(--c-muted);margin-top:8px;">excl. GH₵ {{ number_format($totals['delivery'], 2) }} delivery</p>
        </div>

    </div>

    {{-- ── Chart ── --}}
    @php $chart = $this->getChartData(); @endphp
    <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;overflow:hidden;"
         wire:key="revenue-chart-{{ $dateFrom }}-{{ $dateTo }}-{{ $groupBy }}">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--c-divider);">
            <div>
                <p style="font-size:14px;font-weight:600;color:var(--c-text);">Revenue Over Period</p>
                <p style="font-size:12px;color:var(--c-muted);margin-top:2px;">{{ ucfirst($groupBy) }}-by-{{ $groupBy }} breakdown</p>
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--c-muted);">
                <span style="width:12px;height:2px;background:#dc2626;border-radius:2px;display:inline-block;"></span>
                Revenue (GH₵)
            </div>
        </div>
        <div style="padding:20px;" x-data="{
            chart: null,
            init() {
                if (this.chart) { this.chart.destroy(); this.chart = null; }
                const dark = document.documentElement.classList.contains('dark');
                const ctx = this.$refs.canvas.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chart['labels']) !!},
                        datasets: [{
                            label: 'Revenue (GH₵)',
                            data: {!! json_encode($chart['revenues']) !!},
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220,38,38,0.06)',
                            fill: true, tension: 0.4, borderWidth: 2,
                            pointRadius: 4, pointBackgroundColor: '#dc2626',
                            pointBorderColor: dark ? '#1f2937' : '#fff',
                            pointBorderWidth: 2, pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: dark ? '#111827' : '#fff',
                                borderColor: dark ? '#374151' : '#e5e7eb',
                                borderWidth: 1,
                                titleColor: dark ? '#f9fafb' : '#111827',
                                bodyColor: dark ? '#d1d5db' : '#6b7280',
                                padding: 10,
                                callbacks: { label: c => '  GH₵ ' + c.parsed.y.toLocaleString('en-GH', { minimumFractionDigits: 2 }) }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)' },
                                ticks: { color: dark ? '#6b7280' : '#9ca3af', font: { size: 11 }, callback: v => 'GH₵ ' + v.toLocaleString() }
                            },
                            x: {
                                border: { display: false }, grid: { display: false },
                                ticks: { color: dark ? '#6b7280' : '#9ca3af', font: { size: 11 } }
                            }
                        }
                    }
                });
            }
        }" x-init="init()">
            <div style="position:relative;height:260px;"><canvas x-ref="canvas"></canvas></div>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--c-divider);">
            <p style="font-size:14px;font-weight:600;color:var(--c-text);">Breakdown</p>
            <p style="font-size:12px;color:var(--c-muted);margin-top:2px;">Period-by-period detail</p>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:var(--c-surface);border-bottom:1px solid var(--c-border);">
                        <th style="text-align:left;padding:10px 20px;font-size:11px;font-weight:600;color:var(--c-text2);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">Period</th>
                        <th style="text-align:right;padding:10px 20px;font-size:11px;font-weight:600;color:var(--c-text2);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">Orders</th>
                        <th style="text-align:right;padding:10px 20px;font-size:11px;font-weight:600;color:var(--c-text2);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">Revenue</th>
                        <th style="text-align:right;padding:10px 20px;font-size:11px;font-weight:600;color:var(--c-text2);text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">Delivery</th>
                        <th style="text-align:right;padding:10px 20px;font-size:11px;font-weight:600;color:#059669;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;">Net Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->reportData as $row)
                    @php $net = (float)$row->revenue - (float)($row->delivery_total ?? 0); @endphp
                    <tr style="border-bottom:1px solid var(--c-divider);" onmouseover="this.style.background='var(--c-surface)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 20px;font-weight:500;color:var(--c-text);white-space:nowrap;">{{ $row->period }}</td>
                        <td style="padding:12px 20px;text-align:right;color:var(--c-text2);font-variant-numeric:tabular-nums;">{{ number_format($row->orders) }}</td>
                        <td style="padding:12px 20px;text-align:right;font-weight:600;color:var(--c-text);font-variant-numeric:tabular-nums;">GH₵ {{ number_format($row->revenue, 2) }}</td>
                        <td style="padding:12px 20px;text-align:right;color:var(--c-muted);font-variant-numeric:tabular-nums;">GH₵ {{ number_format($row->delivery_total ?? 0, 2) }}</td>
                        <td style="padding:12px 20px;text-align:right;font-weight:600;color:#059669;font-variant-numeric:tabular-nums;">GH₵ {{ number_format($net, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding:48px 20px;text-align:center;">
                            <svg style="width:32px;height:32px;margin:0 auto 8px;display:block;" fill="none" stroke="var(--c-border)" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg>
                            <p style="font-size:13px;font-weight:500;color:var(--c-muted);">No paid orders in this period</p>
                            <p style="font-size:12px;color:var(--c-border);margin-top:4px;">Try adjusting the date range</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($this->reportData->isNotEmpty())
                <tfoot>
                    <tr style="background:var(--c-surface);border-top:2px solid var(--c-border);">
                        <td style="padding:12px 20px;font-size:11px;font-weight:700;color:var(--c-text2);text-transform:uppercase;letter-spacing:.06em;">Total</td>
                        <td style="padding:12px 20px;text-align:right;font-weight:700;color:var(--c-text);font-variant-numeric:tabular-nums;">{{ number_format($totals['orders']) }}</td>
                        <td style="padding:12px 20px;text-align:right;font-weight:700;color:var(--c-text);font-variant-numeric:tabular-nums;">GH₵ {{ number_format($totals['revenue'], 2) }}</td>
                        <td style="padding:12px 20px;text-align:right;font-weight:700;color:var(--c-muted);font-variant-numeric:tabular-nums;">GH₵ {{ number_format($totals['delivery'], 2) }}</td>
                        <td style="padding:12px 20px;text-align:right;font-weight:700;color:#059669;font-variant-numeric:tabular-nums;">GH₵ {{ number_format($totals['net_revenue'], 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
</x-filament-panels::page>
