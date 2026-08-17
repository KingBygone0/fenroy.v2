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
    public string $delivery_fee         = '0';
    public string $free_delivery_above  = '0';
    public string $ga4_measurement_id    = '';
    public string $paystack_public_key   = '';
    public string $paystack_secret_key   = '';
    public string $paystack_webhook_secret = '';

    public function mount(): void
    {
        $this->store_name              = Setting::get('store_name', '');
        $this->store_tagline           = Setting::get('store_tagline', '');
        $this->contact_email           = Setting::get('contact_email', '');
        $this->contact_phone           = Setting::get('contact_phone', '');
        $this->instagram_url           = Setting::get('instagram_url', '');
        $this->facebook_url            = Setting::get('facebook_url', '');
        $this->whatsapp_number         = Setting::get('whatsapp_number', '');
        $this->banner_enabled          = (bool) Setting::get('banner_enabled', '0');
        $this->banner_message          = Setting::get('banner_message', '');
        $this->minimum_order_amount    = Setting::get('minimum_order_amount', '0');
        $this->delivery_fee            = Setting::get('delivery_fee', '0');
        $this->free_delivery_above     = Setting::get('free_delivery_above', '0');
        $this->ga4_measurement_id      = Setting::get('ga4_measurement_id', '');
        $this->paystack_public_key     = Setting::get('paystack_public_key', '');
        $this->paystack_secret_key     = Setting::get('paystack_secret_key', '');
        $this->paystack_webhook_secret = Setting::get('paystack_webhook_secret', '');
    }

    public function toggleBanner(): void
    {
        $this->banner_enabled = ! $this->banner_enabled;
        Setting::set('banner_enabled', $this->banner_enabled ? '1' : '0');
        Notification::make()
            ->title($this->banner_enabled ? 'Banner enabled' : 'Banner disabled')
            ->success()
            ->send();
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
            'delivery_fee'         => 'nullable|numeric|min:0',
            'free_delivery_above'  => 'nullable|numeric|min:0',
            'ga4_measurement_id'      => ['nullable', 'string', 'max:30', function ($attr, $val, $fail) {
                if ($val !== '' && ! preg_match('/^G-[A-Z0-9]+$/i', $val)) {
                    $fail('Must be a valid GA4 Measurement ID (e.g. G-XXXXXXXXXX).');
                }
            }],
            'paystack_public_key'     => 'nullable|string|max:100',
            'paystack_secret_key'     => 'nullable|string|max:100',
            'paystack_webhook_secret' => 'nullable|string|max:100',
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
        Setting::set('delivery_fee', $this->delivery_fee);
        Setting::set('free_delivery_above', $this->free_delivery_above);
        Setting::set('ga4_measurement_id', $this->ga4_measurement_id);
        Setting::set('paystack_public_key', $this->paystack_public_key);
        Setting::set('paystack_secret_key', $this->paystack_secret_key);
        Setting::set('paystack_webhook_secret', $this->paystack_webhook_secret);

        Notification::make()->title('Settings saved')->success()->send();
    }
}
