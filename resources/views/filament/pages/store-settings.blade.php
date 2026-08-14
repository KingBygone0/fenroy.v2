<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Store Identity --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Store Identity</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Store Name <span style="color:#ef4444;">*</span></label>
                    <input wire:model="store_name" type="text" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                    @error('store_name') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Tagline</label>
                    <input wire:model="store_tagline" type="text" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Contact</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Email</label>
                    <input wire:model="contact_email" type="email" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                    @error('contact_email') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Phone</label>
                    <input wire:model="contact_phone" type="text" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                </div>
            </div>
        </div>

        {{-- Social Links --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Social Links</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Instagram URL</label>
                    <input wire:model="instagram_url" type="url" placeholder="https://instagram.com/..." style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                    @error('instagram_url') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Facebook URL</label>
                    <input wire:model="facebook_url" type="url" placeholder="https://facebook.com/..." style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                    @error('facebook_url') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">WhatsApp Number</label>
                    <input wire:model="whatsapp_number" type="text" placeholder="+233..." style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                </div>
            </div>
        </div>

        {{-- Announcement Banner --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Announcement Banner</h2>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <label style="font-size:13px;font-weight:500;color:#374151;">Enable Banner</label>
                <button type="button" wire:click="$toggle('banner_enabled')"
                    style="position:relative;display:inline-flex;width:44px;height:24px;border-radius:9999px;transition:background .2s;background:{{ $banner_enabled ? '#E53935' : '#d1d5db' }};border:none;cursor:pointer;">
                    <span style="position:absolute;top:2px;left:{{ $banner_enabled ? '22px' : '2px' }};width:20px;height:20px;background:white;border-radius:9999px;transition:left .2s;"></span>
                </button>
                <span style="font-size:13px;color:#6b7280;">{{ $banner_enabled ? 'On' : 'Off' }}</span>
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Banner Message</label>
                <textarea wire:model="banner_message" rows="2" placeholder="e.g. Free delivery this weekend on orders over GH₵ 100!" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;resize:vertical;"></textarea>
                @error('banner_message') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Order Rules --}}
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Order Rules</h2>
            <div style="max-width:240px;">
                <label style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Minimum Order Amount (GH₵)</label>
                <input wire:model="minimum_order_amount" type="number" min="0" step="0.01" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;" />
                @error('minimum_order_amount') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <button type="submit" style="padding:10px 24px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                Save Settings
            </button>
        </div>

    </form>
</x-filament-panels::page>
