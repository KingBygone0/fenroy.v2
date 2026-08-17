# Admin Completeness Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fill every identified gap in the Fenroy admin panel — configurable delivery fee, first coupon, SMS balance widget, stock adjustment, delivery zone nav cleanup, customer analytics, and Google Analytics wiring.

**Architecture:** All changes are self-contained additions to existing Filament resources/pages/widgets. No new database tables are needed. Settings are stored via the existing `Setting::get/set` key-value model. No bulk order status or order tracking changes are needed — those are already complete.

**Tech Stack:** Laravel 13, Filament v5, Livewire 3, Alpine.js, Tailwind CSS, Arkesel SMS API v2, Chart.js (CDN already loaded on revenue report page).

**Spec:** `docs/superpowers/plans/2026-08-15-admin-completeness.md` (this file)

## Global Constraints

- Currency: GH₵, locale formatting with `number_format($v, 2)`
- PHP 8.3 on server; no named arguments on built-ins not yet supported
- Settings stored via `App\Models\Setting::get($key, $default)` / `Setting::set($key, $value)`
- Deploy via Paramiko SFTP — never git pull on server
- After every deploy: `php artisan cache:clear && php artisan view:clear && php artisan config:clear`
- Navigation groups: Commerce, Administration
- No new migrations — use existing tables only

---

## File Map

| Task | Files modified / created |
|------|--------------------------|
| 1 — Delivery fee in Settings | `app/Filament/Pages/StoreSettings.php`, `resources/views/filament/pages/store-settings.blade.php`, `app/Livewire/CheckoutPage.php` |
| 2 — Seed welcome coupon | Server-side: `php artisan tinker` one-liner on server via SSH |
| 3 — SMS balance widget | Create `app/Filament/Widgets/SmsBalanceWidget.php` |
| 4 — Stock adjustment action | `app/Filament/Resources/ProductResource.php` |
| 5 — Hide Delivery Zones nav | `app/Filament/Resources/DeliveryZoneResource.php` |
| 6 — Customer analytics widget | Create `app/Filament/Widgets/CustomerAnalyticsWidget.php` |
| 7 — Google Analytics | `app/Filament/Pages/StoreSettings.php`, `resources/views/filament/pages/store-settings.blade.php`, `resources/views/components/layouts/storefront.blade.php` |

---

### Task 1: Delivery fee configurable from Store Settings

**Files:**
- Modify: `app/Filament/Pages/StoreSettings.php`
- Modify: `resources/views/filament/pages/store-settings.blade.php`
- Modify: `app/Livewire/CheckoutPage.php`

**Context:**  
`StoreSettings.php` manages key-value pairs via `Setting::get/set`. `CheckoutPage::deliveryFee()` currently reads from a DeliveryZone model. We replace the zone-based fee lookup with a flat fee read from settings.

- [ ] **Step 1: Add two public properties to StoreSettings**

In `app/Filament/Pages/StoreSettings.php`, add after `public string $minimum_order_amount`:
```php
public string $delivery_fee         = '10';
public string $free_delivery_above  = '';
```

- [ ] **Step 2: Load them in mount()**

Add after `$this->minimum_order_amount = Setting::get(...)`:
```php
$this->delivery_fee        = Setting::get('delivery_fee', '10');
$this->free_delivery_above = Setting::get('free_delivery_above', '');
```

- [ ] **Step 3: Add validation rules in save()**

In the `validate()` array, add:
```php
'delivery_fee'        => 'required|numeric|min:0',
'free_delivery_above' => 'nullable|numeric|min:0',
```

- [ ] **Step 4: Persist them in save()**

After `Setting::set('minimum_order_amount', ...)`:
```php
Setting::set('delivery_fee', $this->delivery_fee);
Setting::set('free_delivery_above', $this->free_delivery_above);
```

- [ ] **Step 5: Add fields to the blade view**

In `resources/views/filament/pages/store-settings.blade.php`, find the section with `minimum_order_amount` and add two new inputs alongside it:
```html
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delivery Fee (GH₵)</label>
    <input type="number" step="0.01" wire:model.live="delivery_fee"
        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
    <p class="text-xs text-gray-400 mt-1">Flat fee charged on every order.</p>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Free Delivery Above (GH₵)</label>
    <input type="number" step="0.01" wire:model.live="free_delivery_above"
        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
        placeholder="Leave blank to always charge">
    <p class="text-xs text-gray-400 mt-1">Orders above this amount get free delivery.</p>
</div>
```

- [ ] **Step 6: Update CheckoutPage::deliveryFee()**

Replace the entire `deliveryFee()` computed method in `app/Livewire/CheckoutPage.php`:
```php
#[Computed]
public function deliveryFee(): float
{
    $fee       = (float) \App\Models\Setting::get('delivery_fee', 10);
    $freeAbove = \App\Models\Setting::get('free_delivery_above', '');
    if ($freeAbove !== '' && $this->subtotal >= (float) $freeAbove) {
        return 0.00;
    }
    return $fee;
}
```

Also remove the `selectedZone()` computed method and the `zoneId` property if they are no longer referenced anywhere in the blade. (**Check first** with `grep -n "zoneId\|selectedZone\|DeliveryZone" resources/views/livewire/checkout-page.blade.php` — if the blade references them, leave them in place and just override the fee calculation.)

- [ ] **Step 7: Deploy and clear caches**

Upload the three modified files via Paramiko. Then on server:
```
php artisan cache:clear && php artisan view:clear && php artisan config:clear
```

- [ ] **Step 8: Verify in admin**

Open `/admin/store-settings` — confirm the two new fields appear. Save with `delivery_fee = 10` and `free_delivery_above = 150`. Open the checkout page and confirm the delivery fee shows GH₵10 and disappears when cart subtotal exceeds GH₵150.

---

### Task 2: Seed the WELCOME10 coupon

**Files:** None (server DB write via SSH)

**Context:** The `coupons` table exists and the `Coupon` model is complete. We just need at least one real code in the DB so the coupon feature works for customers.

- [ ] **Step 1: Run via SSH Paramiko**

Execute this Python snippet to create the coupon on the live server:
```python
import paramiko
client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('194.164.71.237', port=65002, username='u691159314', password='u2i-nMH-fm4-Tqb')
cmd = (
    "cd /home/u691159314/domains/fenroy.shop && "
    "php artisan tinker --execute=\""
    "\\App\\Models\\Coupon::firstOrCreate(['code'=>'WELCOME10'],["
    "'type'=>'percent','value'=>10,'min_order'=>50,'max_uses'=>null,'used_count'=>0,'is_active'=>1"
    "]);echo 'done';\""
)
_, o, e = client.exec_command(cmd)
print(o.read().decode(), e.read().decode())
client.close()
```

- [ ] **Step 2: Verify in admin**

Open `/admin/coupons` — confirm WELCOME10 appears with type=percent, value=10, active=yes.

- [ ] **Step 3: Create a FREESHIP coupon too**

Run a second tinker command for a free-delivery coupon (fixed GH₵10 off):
```python
cmd = (
    "cd /home/u691159314/domains/fenroy.shop && "
    "php artisan tinker --execute=\""
    "\\App\\Models\\Coupon::firstOrCreate(['code'=>'FREESHIP'],["
    "'type'=>'fixed','value'=>10,'min_order'=>80,'max_uses'=>null,'used_count'=>0,'is_active'=>1"
    "]);echo 'done';\""
)
```

---

### Task 3: SMS balance widget

**Files:**
- Create: `app/Filament/Widgets/SmsBalanceWidget.php`

**Context:** Arkesel API v2. Balance endpoint: `GET https://sms.arkesel.com/api/v2/clients/balance-details` with header `api-key: <ARKESEL_API_KEY>`. Returns JSON with `data.sms_balance`. Widget shows balance with a warning color if below 20 units.

- [ ] **Step 1: Create the widget**

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Http;

class SmsBalanceWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected static ?string $pollingInterval = null;

    protected array|string|int $columnSpan = 'full';

    protected function getStats(): array
    {
        $balance = $this->fetchBalance();

        return [
            Stat::make('SMS Balance (Arkesel)', $balance === null ? 'Unavailable' : (string) $balance . ' units')
                ->description($balance === null
                    ? 'Could not reach Arkesel API'
                    : ($balance < 20 ? 'Low — top up soon' : 'Sufficient'))
                ->descriptionIcon($balance !== null && $balance < 20
                    ? 'heroicon-m-exclamation-triangle'
                    : 'heroicon-m-chat-bubble-left-right')
                ->color($balance === null ? 'gray' : ($balance < 20 ? 'danger' : 'success')),
        ];
    }

    private function fetchBalance(): ?int
    {
        try {
            $response = Http::withHeaders([
                'api-key' => config('arkesel.api_key'),
            ])->timeout(5)->get('https://sms.arkesel.com/api/v2/clients/balance-details');

            if ($response->successful()) {
                return (int) data_get($response->json(), 'data.sms_balance', 0);
            }
        } catch (\Throwable) {
        }
        return null;
    }
}
```

- [ ] **Step 2: Register in AdminPanelProvider**

In `app/Providers/Filament/AdminPanelProvider.php`, add to the `widgets()` array:
```php
\App\Filament\Widgets\SmsBalanceWidget::class,
```

- [ ] **Step 3: Deploy both files via Paramiko and clear caches**

- [ ] **Step 4: Verify**

Open `/admin` dashboard — confirm "SMS Balance (Arkesel)" stat card appears with a unit count. If the API key is wrong or unreachable, it shows "Unavailable" in gray.

---

### Task 4: Manual stock adjustment on Products

**Files:**
- Modify: `app/Filament/Resources/ProductResource.php`

**Context:** ProductResource already has `EditAction`. We add a row-level `Action` that pops a modal with a signed integer input (e.g. `+10` or `-3`) and applies it to `stock`, with a floor of 0. Uses Filament's `Action::make()` with a `form()` modal.

- [ ] **Step 1: Add the action to the table actions array**

In the `table()` method of `ProductResource`, locate `->actions([` and add before `EditAction::make()`:

```php
Action::make('adjustStock')
    ->label('Adjust Stock')
    ->icon('heroicon-m-arrows-up-down')
    ->color('gray')
    ->form([
        TextInput::make('adjustment')
            ->label('Adjustment')
            ->helperText('Use positive (+5) to add stock, negative (-3) to reduce.')
            ->required()
            ->integer()
            ->default(0),
    ])
    ->action(function (Product $record, array $data): void {
        $newStock = max(0, $record->stock + (int) $data['adjustment']);
        $record->update(['stock' => $newStock]);
        Notification::make()
            ->title('Stock updated to ' . $newStock . ' units')
            ->success()
            ->send();
    }),
```

Make sure `Action`, `TextInput`, `Notification` are already imported at the top of the file (they should be — check and add any missing `use` statements).

- [ ] **Step 2: Deploy and clear caches**

- [ ] **Step 3: Verify**

Open `/admin/products`, click the `⇅ Adjust Stock` button on any product, enter `-2`, submit. Confirm stock decremented. Enter `+5`, confirm it incremented. Enter a large negative that would go below 0 — confirm it floors at 0.

---

### Task 5: Hide Delivery Zones from navigation

**Files:**
- Modify: `app/Filament/Resources/DeliveryZoneResource.php`

**Context:** Delivery is now a flat fee from Store Settings. The Delivery Zones resource still exists in code (safe to keep for potential future use) but the nav link is clutter.

- [ ] **Step 1: Add shouldRegisterNavigation override**

In `DeliveryZoneResource`, add after the `$navigationSort` property:
```php
public static function shouldRegisterNavigation(): bool
{
    return false;
}
```

- [ ] **Step 2: Deploy and clear caches**

- [ ] **Step 3: Verify**

Open `/admin` — confirm "Delivery Zones" no longer appears in the left nav under Commerce.

---

### Task 6: Customer analytics widget

**Files:**
- Create: `app/Filament/Widgets/CustomerAnalyticsWidget.php`

**Context:** "Returning customer" = a `customer_email` that appears in more than one paid order. "New customer" = first-time buyer in the selected window. The widget shows: total unique customers, new vs returning split, and top 5 repeat buyers in the last 30 days.

- [ ] **Step 1: Create the widget**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected static ?string $pollingInterval = null;

    protected array|string|int $columnSpan = 'full';

    protected function getStats(): array
    {
        $since = now()->subDays(30);

        // All unique paying customers ever
        $totalCustomers = Order::where('payment_status', 'paid')
            ->distinct('customer_email')
            ->count('customer_email');

        // Emails that appear in more than one paid order (returning)
        $returningEmails = Order::where('payment_status', 'paid')
            ->select('customer_email')
            ->groupBy('customer_email')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('customer_email');

        $returningCount = $returningEmails->count();
        $newCount       = $totalCustomers - $returningCount;

        // Repeat rate: returning / total
        $repeatRate = $totalCustomers > 0
            ? round(($returningCount / $totalCustomers) * 100)
            : 0;

        // New customers in last 30 days (first-ever order falls in window)
        $newLast30 = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $since)
            ->select('customer_email')
            ->groupBy('customer_email')
            ->get()
            ->filter(function ($row) use ($since) {
                $firstOrder = Order::where('customer_email', $row->customer_email)
                    ->where('payment_status', 'paid')
                    ->orderBy('created_at')
                    ->value('created_at');
                return $firstOrder >= $since;
            })
            ->count();

        return [
            Stat::make('Total Unique Customers', number_format($totalCustomers))
                ->description('All-time paying customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Repeat Customer Rate', $repeatRate . '%')
                ->description("{$returningCount} returning of {$totalCustomers}")
                ->descriptionIcon($repeatRate >= 30 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($repeatRate >= 30 ? 'success' : 'warning'),

            Stat::make('New Customers (30 days)', number_format($newLast30))
                ->description('First-time buyers this month')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
        ];
    }
}
```

- [ ] **Step 2: Register in AdminPanelProvider**

Add to `widgets()` array:
```php
\App\Filament\Widgets\CustomerAnalyticsWidget::class,
```

- [ ] **Step 3: Deploy and clear caches**

- [ ] **Step 4: Verify**

Open `/admin` — confirm three new customer stat cards appear. If no paid orders exist, all will show 0/0%.

---

### Task 7: Google Analytics GA4

**Files:**
- Modify: `app/Filament/Pages/StoreSettings.php`
- Modify: `resources/views/filament/pages/store-settings.blade.php`
- Modify: `resources/views/components/layouts/storefront.blade.php`

**Context:** GA4 Measurement ID format is `G-XXXXXXXXXX`. It's stored as a setting and injected into the storefront `<head>` only when non-empty. The admin panel itself does not get the tag.

- [ ] **Step 1: Add property to StoreSettings**

Add after `$free_delivery_above`:
```php
public string $ga4_measurement_id = '';
```

- [ ] **Step 2: Load in mount()**

```php
$this->ga4_measurement_id = Setting::get('ga4_measurement_id', '');
```

- [ ] **Step 3: Add validation**

```php
'ga4_measurement_id' => 'nullable|string|max:30|regex:/^(G-[A-Z0-9]+)?$/',
```

- [ ] **Step 4: Persist in save()**

```php
Setting::set('ga4_measurement_id', trim($this->ga4_measurement_id));
```

- [ ] **Step 5: Add input field to store-settings blade**

Add a new input in `resources/views/filament/pages/store-settings.blade.php` in the appropriate settings section:
```html
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Google Analytics 4 Measurement ID</label>
    <input type="text" wire:model.live="ga4_measurement_id"
        placeholder="G-XXXXXXXXXX"
        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
    <p class="text-xs text-gray-400 mt-1">Leave blank to disable tracking.</p>
</div>
```

- [ ] **Step 6: Inject GA4 script in storefront layout**

In `resources/views/components/layouts/storefront.blade.php`, inside `<head>` just before `</head>` (around line 61):
```html
@php $ga4Id = \App\Models\Setting::get('ga4_measurement_id', ''); @endphp
@if($ga4Id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $ga4Id }}');
</script>
@endif
```

- [ ] **Step 7: Deploy three files and clear caches**

- [ ] **Step 8: Verify**

Open Store Settings in admin — confirm the GA4 field appears. Enter `G-TEST123456`, save. View page source on the storefront homepage — confirm the `gtag` script block is present with the correct ID. Clear the field and confirm the script block disappears.

---

## Deploy Order

Tasks 1–7 are independent. Recommended order for minimal risk:

1. Task 5 (hide nav — zero risk, no logic change)
2. Task 2 (seed coupons — no code change)
3. Task 4 (stock adjustment — additive only)
4. Task 3 (SMS widget — additive only)
5. Task 6 (customer analytics — additive only)
6. Task 1 (delivery fee — touches checkout, test carefully)
7. Task 7 (GA4 — touches layout, test page source after deploy)
