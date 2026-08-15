<style>
:root {
    --c-bg:#ffffff; --c-border:#e5e7eb; --c-text:#111827;
    --c-muted:#6b7280; --c-surface:#f9fafb; --c-divider:#f3f4f6;
}
html.dark {
    --c-bg:#1f2937; --c-border:#374151; --c-text:#f9fafb;
    --c-muted:#9ca3af; --c-surface:#111827; --c-divider:#374151;
}
</style>

<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Profile card --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--c-text);margin-bottom:16px;">Profile</h2>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                <div>
                    <p style="font-size:12px;color:var(--c-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Name</p>
                    <p style="font-size:15px;color:var(--c-text);font-weight:500;">{{ $this->record->name }}</p>
                </div>
                <div>
                    <p style="font-size:12px;color:var(--c-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Email</p>
                    <p style="font-size:15px;color:var(--c-text);font-weight:500;">{{ $this->record->email }}</p>
                </div>
                <div>
                    <p style="font-size:12px;color:var(--c-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Phone</p>
                    <p style="font-size:15px;color:var(--c-text);font-weight:500;">{{ $this->record->phone ?? '—' }}</p>
                </div>
                <div>
                    <p style="font-size:12px;color:var(--c-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Joined</p>
                    <p style="font-size:15px;color:var(--c-text);font-weight:500;">{{ $this->record->created_at->format('d M Y') }}</p>
                </div>
                @if($this->record->is_admin)
                <div>
                    <span style="background:#fef3c7;color:#d97706;padding:3px 10px;border-radius:9999px;font-size:12px;font-weight:600;">Admin</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        @php $stats = $this->getStats(); @endphp
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
            <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:20px;text-align:center;">
                <p style="font-size:28px;font-weight:800;color:var(--c-text);">{{ $stats['total_orders'] }}</p>
                <p style="font-size:13px;color:var(--c-muted);margin-top:4px;">Total Orders</p>
            </div>
            <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:20px;text-align:center;">
                <p style="font-size:28px;font-weight:800;color:var(--c-text);">GH₵ {{ number_format($stats['total_spent'], 2) }}</p>
                <p style="font-size:13px;color:var(--c-muted);margin-top:4px;">Total Spent (paid orders)</p>
            </div>
        </div>

        {{-- Order history --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:16px;font-weight:700;color:var(--c-text);margin-bottom:16px;">Order History</h2>
            {!! $this->getOrdersHtml() !!}
        </div>

    </div>
</x-filament-panels::page>
