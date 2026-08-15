<style>
:root {
    --c-bg:#ffffff; --c-border:#e5e7eb; --c-text:#111827; --c-label:#374151;
    --c-muted:#6b7280; --c-input-bg:#ffffff; --c-input-border:#d1d5db;
}
html.dark {
    --c-bg:#1f2937; --c-border:#374151; --c-text:#f9fafb; --c-label:#d1d5db;
    --c-muted:#9ca3af; --c-input-bg:#111827; --c-input-border:#4b5563;
}
</style>

<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Store Identity --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:var(--c-text);margin-bottom:16px;">Store Identity</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Store Name <span style="color:#ef4444;">*</span></label>
                    <input wire:model="store_name" type="text" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                    @error('store_name') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Tagline</label>
                    <input wire:model="store_tagline" type="text" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:var(--c-text);margin-bottom:16px;">Contact</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Email</label>
                    <input wire:model="contact_email" type="email" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                    @error('contact_email') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Phone</label>
                    <input wire:model="contact_phone" type="text" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                </div>
            </div>
        </div>

        {{-- Social Links --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:var(--c-text);margin-bottom:16px;">Social Links</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Instagram URL</label>
                    <input wire:model="instagram_url" type="url" placeholder="https://instagram.com/..." style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                    @error('instagram_url') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Facebook URL</label>
                    <input wire:model="facebook_url" type="url" placeholder="https://facebook.com/..." style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                    @error('facebook_url') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">WhatsApp Number</label>
                    <input wire:model="whatsapp_number" type="text" placeholder="+233..." style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                </div>
            </div>
        </div>

        {{-- Announcement Banner --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:var(--c-text);margin-bottom:16px;">Announcement Banner</h2>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <label style="font-size:13px;font-weight:500;color:var(--c-label);">Enable Banner</label>
                <button type="button" wire:click="$toggle('banner_enabled')"
                    style="position:relative;display:inline-flex;width:44px;height:24px;border-radius:9999px;transition:background .2s;background:{{ $banner_enabled ? '#E53935' : '#d1d5db' }};border:none;cursor:pointer;">
                    <span style="position:absolute;top:2px;left:{{ $banner_enabled ? '22px' : '2px' }};width:20px;height:20px;background:white;border-radius:9999px;transition:left .2s;"></span>
                </button>
                <span style="font-size:13px;color:var(--c-muted);">{{ $banner_enabled ? 'On' : 'Off' }}</span>
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Banner Message</label>
                <textarea wire:model="banner_message" rows="2" placeholder="e.g. Free delivery this weekend on orders over GH₵ 100!" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;resize:vertical;background:var(--c-input-bg);color:var(--c-text);"></textarea>
                @error('banner_message') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Order Rules --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:var(--c-text);margin-bottom:16px;">Order Rules</h2>
            <div style="max-width:240px;">
                <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Minimum Order Amount (GH₵)</label>
                <input wire:model="minimum_order_amount" type="number" min="0" step="0.01" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                @error('minimum_order_amount') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Delivery --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:var(--c-text);margin-bottom:4px;">Delivery</h2>
            <p style="font-size:12px;color:var(--c-muted);margin-bottom:16px;">Flat fee applied when no delivery zone is matched. Free delivery threshold overrides both zones and the flat fee globally.</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:500px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Default Delivery Fee (GH₵)</label>
                    <input wire:model="delivery_fee" type="number" min="0" step="0.01" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                    @error('delivery_fee') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Free Delivery Above (GH₵, 0 = off)</label>
                    <input wire:model="free_delivery_above" type="number" min="0" step="0.01" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);" />
                    @error('free_delivery_above') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Analytics & Tracking --}}
        <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:var(--c-text);margin-bottom:4px;">Analytics &amp; Tracking</h2>
            <p style="font-size:12px;color:var(--c-muted);margin-bottom:16px;">Leave blank to disable. Measurement IDs start with <code style="background:var(--c-surface);padding:1px 5px;border-radius:4px;">G-</code></p>
            <div style="max-width:280px;">
                <label style="display:block;font-size:13px;font-weight:500;color:var(--c-label);margin-bottom:6px;">Google Analytics 4 Measurement ID</label>
                <input wire:model="ga4_measurement_id" type="text" placeholder="G-XXXXXXXXXX" style="width:100%;padding:9px 12px;border:1px solid var(--c-input-border);border-radius:8px;font-size:14px;outline:none;background:var(--c-input-bg);color:var(--c-text);font-family:monospace;" />
                @error('ga4_measurement_id') <p style="color:#ef4444;font-size:12px;margin-top:4px;">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <button type="submit" style="padding:10px 24px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                Save Settings
            </button>
        </div>

    </form>
</x-filament-panels::page>
