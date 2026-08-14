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
