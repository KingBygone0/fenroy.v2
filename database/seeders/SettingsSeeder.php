<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'store_name'           => 'Fenroy Supermarket',
            'store_tagline'        => 'Your everyday online market',
            'contact_email'        => '',
            'contact_phone'        => '',
            'instagram_url'        => '',
            'facebook_url'         => '',
            'whatsapp_number'      => '',
            'banner_enabled'       => '0',
            'banner_message'       => '',
            'minimum_order_amount' => '0',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
