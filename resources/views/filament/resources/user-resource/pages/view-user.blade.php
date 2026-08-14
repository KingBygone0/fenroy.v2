<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Profile card --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:16px;font-weight:700;color:#111827;margin-bottom:16px;">Profile</h2>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
                <div>
                    <p style="font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Name</p>
                    <p style="font-size:15px;color:#111827;font-weight:500;">{{ $this->record->name }}</p>
                </div>
                <div>
                    <p style="font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Email</p>
                    <p style="font-size:15px;color:#111827;font-weight:500;">{{ $this->record->email }}</p>
                </div>
                <div>
                    <p style="font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Phone</p>
                    <p style="font-size:15px;color:#111827;font-weight:500;">{{ $this->record->phone ?? '—' }}</p>
                </div>
                <div>
                    <p style="font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Joined</p>
                    <p style="font-size:15px;color:#111827;font-weight:500;">{{ $this->record->created_at->format('d M Y') }}</p>
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
            <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:20px;text-align:center;">
                <p style="font-size:28px;font-weight:800;color:#111827;">{{ $stats['total_orders'] }}</p>
                <p style="font-size:13px;color:#6b7280;margin-top:4px;">Total Orders</p>
            </div>
            <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:20px;text-align:center;">
                <p style="font-size:28px;font-weight:800;color:#111827;">GH₵ {{ number_format($stats['total_spent'], 2) }}</p>
                <p style="font-size:13px;color:#6b7280;margin-top:4px;">Total Spent (paid orders)</p>
            </div>
        </div>

        {{-- Order history --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:16px;font-weight:700;color:#111827;margin-bottom:16px;">Order History</h2>
            {!! $this->getOrdersHtml() !!}
        </div>

    </div>
</x-filament-panels::page>
