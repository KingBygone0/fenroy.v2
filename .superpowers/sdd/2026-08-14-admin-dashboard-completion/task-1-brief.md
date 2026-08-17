# Task 1 Brief: Settings Infrastructure

## Context
You are implementing Task 1 of 8 in the Fenroy admin dashboard completion. This is a Laravel 13 / Filament v5 project at C:\Users\King\Documents\LYNJAY\laravel\fenroy. The `settings` table and `Setting` model you create here are consumed by Task 6 (StoreSettings page).

## Requirements
Create four files exactly as specified below:

### File 1: `database/migrations/2026_08_14_000001_create_settings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
```

### File 2: `app/Models/Setting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private static ?Collection $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (static::$cache === null) {
            static::$cache = static::all()->pluck('value', 'key');
        }

        return static::$cache->get($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$cache = null;
    }
}
```

### File 3: `database/seeders/SettingsSeeder.php`

```php
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
```

### File 4: Modify `database/seeders/DatabaseSeeder.php`

Read the existing DatabaseSeeder.php file first. Add `SettingsSeeder::class` to the `$this->call([...])` array. If no call array exists, add one.

## Steps
1. Write the migration file
2. Run `php artisan migrate` — confirm `settings` table created
3. Write `app/Models/Setting.php`
4. Write `database/seeders/SettingsSeeder.php`
5. Modify `database/seeders/DatabaseSeeder.php` to register SettingsSeeder
6. Run `php artisan db:seed --class=SettingsSeeder` — confirm 10 rows seeded
7. Smoke-test in tinker:
   - `App\Models\Setting::get('store_name')` → "Fenroy Supermarket"
   - `App\Models\Setting::set('store_name', 'Test')` then `get()` → "Test"
   - Reset: `App\Models\Setting::set('store_name', 'Fenroy Supermarket')`
8. Commit: `git add database/migrations/2026_08_14_000001_create_settings_table.php database/seeders/SettingsSeeder.php app/Models/Setting.php database/seeders/DatabaseSeeder.php && git commit -m "feat: add settings key-value infrastructure"`

## Report file
Write your report to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-1-report.md`

Return only: status (DONE/BLOCKED/NEEDS_CONTEXT), commit hash, test output one-liner, any concerns.
