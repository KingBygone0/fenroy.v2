# Task 6 Brief: Store Settings Page + Storefront Banner

## Context
Task 6 of 8. Laravel 13 / Filament v5 at C:\Users\King\Documents\LYNJAY\laravel\fenroy.

**Depends on Task 1:** `App\Models\Setting` already exists with `Setting::get(string $key, mixed $default = null): mixed` and `Setting::set(string $key, mixed $value): void`. The settings table has been seeded with 10 keys.

**Important:** Filament auto-discovers pages from `app/Filament/Pages/` — no need to touch AdminPanelProvider.

## File 1: Create `app/Filament/Pages/StoreSettings.php`

```php
<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StoreSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Store Settings';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.store-settings';

    public string $store_name           = '';
    public string $store_tagline        = '';
    public string $contact_email        = '';
    public string $contact_phone        = '';
    public string $instagram_url        = '';
    public string $facebook_url         = '';
    public string $whatsapp_number      = '';
    public bool   $banner_enabled       = false;
    public string $banner_message       = '';
    public string $minimum_order_amount = '0';

    public function mount(): void
    {
        $this->store_name           = Setting::get('store_name', '');
        $this->store_tagline        = Setting::get('store_tagline', '');
        $this->contact_email        = Setting::get('contact_email', '');
        $this->contact_phone        = Setting::get('contact_phone', '');
        $this->instagram_url        = Setting::get('instagram_url', '');
        $this->facebook_url         = Setting::get('facebook_url', '');
        $this->whatsapp_number      = Setting::get('whatsapp_number', '');
        $this->banner_enabled       = (bool) Setting::get('banner_enabled', '0');
        $this->banner_message       = Setting::get('banner_message', '');
        $this->minimum_order_amount = Setting::get('minimum_order_amount', '0');
    }

    public function save(): void
    {
        $this->validate([
            'store_name'           => 'required|string|max:100',
            'store_tagline'        => 'nullable|string|max:150',
            'contact_email'        => 'nullable|email|max:100',
            'contact_phone'        => 'nullable|string|max:30',
            'instagram_url'        => 'nullable|url|max:200',
            'facebook_url'         => 'nullable|url|max:200',
            'whatsapp_number'      => 'nullable|string|max:30',
            'banner_message'       => 'nullable|string|max:300',
            'minimum_order_amount' => 'nullable|numeric|min:0',
        ]);

        Setting::set('store_name', $this->store_name);
        Setting::set('store_tagline', $this->store_tagline);
        Setting::set('contact_email', $this->contact_email);
        Setting::set('contact_phone', $this->contact_phone);
        Setting::set('instagram_url', $this->instagram_url);
        Setting::set('facebook_url', $this->facebook_url);
        Setting::set('whatsapp_number', $this->whatsapp_number);
        Setting::set('banner_enabled', $this->banner_enabled ? '1' : '0');
        Setting::set('banner_message', $this->banner_message);
        Setting::set('minimum_order_amount', $this->minimum_order_amount);

        Notification::make()->title('Settings saved')->success()->send();
    }
}
```

## File 2: Create `resources/views/filament/pages/store-settings.blade.php`

```blade
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
```

## File 3: Modify `resources/views/layouts/storefront.blade.php`

Read the existing file. Add this block at the very top of `<body>`, BEFORE the desktop `<header>` tag (which starts `<header class="hidden md:block...`):

```blade
{{-- ─── Announcement Banner ──────────────────────────────── --}}
@php
    $bannerEnabled = \App\Models\Setting::get('banner_enabled', '0');
    $bannerMessage = \App\Models\Setting::get('banner_message', '');
@endphp
@if($bannerEnabled === '1' && $bannerMessage)
<div class="w-full bg-brand-red text-white text-center text-sm font-medium py-2 px-4">
    {{ $bannerMessage }}
</div>
@endif
```

## Verify
`php -l app/Filament/Pages/StoreSettings.php`

## Commit
```bash
git add app/Filament/Pages/StoreSettings.php resources/views/filament/pages/store-settings.blade.php resources/views/layouts/storefront.blade.php
git commit -m "feat: add store settings admin page with announcement banner"
```

## Report
Write to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-6-report.md`
Return: "Status: DONE, commit: [hash]" or "Status: BLOCKED, reason: [reason]"
