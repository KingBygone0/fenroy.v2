# Admin Dashboard Completion — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete the Fenroy Filament admin panel with 8 feature groups: settings infrastructure, order bulk actions + CSV export enhancement, coupon analytics, customer detail view, product performance widget, store settings page + storefront banner, wishlist & address resources, and product bulk import.

**Architecture:** All features extend or add to the existing Filament v5 admin panel following established codebase patterns (`Filament\Schemas\Schema`, `Filament\Actions\*`). Settings use a key-value `settings` table with a static-cached model. Product performance aggregates JSON order items in PHP. Bulk import uses `maatwebsite/excel` with a custom Filament page.

**Tech Stack:** Laravel 13, Filament v5, Livewire, MySQL, `maatwebsite/excel` (new)

**Spec:** `docs/superpowers/specs/2026-08-14-admin-dashboard-completion-design.md`

## Global Constraints
- Currency prefix: `'GH₵ '` (with trailing space) in all displays
- Filament form schemas use `Filament\Schemas\Schema` — NOT `Filament\Forms\Form`
- All actions import from `Filament\Actions\*` namespace (matching existing resources)
- `Product::category` is stored as a plain string (category name), not a FK
- Order `items` is a JSON array: each element has `name` (string), `quantity` (int), `price` (float)
- Orders link to users by `customer_email`, not `user_id`
- `Address` fields: `user_id`, `full_name`, `phone`, `line1`, `city`, `region`, `is_default`
- No changes to storefront auth, routing, or checkout flow
- Import is synchronous — no queues

---

### Task 1: Settings Infrastructure

**Files:**
- Create: `database/migrations/2026_08_14_000001_create_settings_table.php`
- Create: `database/seeders/SettingsSeeder.php`
- Create: `app/Models/Setting.php`
- Modify: `database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Produces: `Setting::get(string $key, mixed $default = null): mixed` — used by Tasks 6 and the storefront banner
- Produces: `Setting::set(string $key, mixed $value): void` — used by Task 6

- [ ] **Step 1: Create the migration**

File: `database/migrations/2026_08_14_000001_create_settings_table.php`

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

- [ ] **Step 2: Run the migration**

```bash
php artisan migrate
```

Expected: `settings` table created with no errors.

- [ ] **Step 3: Create the Setting model**

File: `app/Models/Setting.php`

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

- [ ] **Step 4: Create the seeder**

File: `database/seeders/SettingsSeeder.php`

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

- [ ] **Step 5: Register the seeder in DatabaseSeeder**

In `database/seeders/DatabaseSeeder.php`, add `SettingsSeeder::class` to the `$this->call([...])` array. If no call array exists yet, add:

```php
$this->call([
    SettingsSeeder::class,
]);
```

- [ ] **Step 6: Seed the settings**

```bash
php artisan db:seed --class=SettingsSeeder
```

Expected: 10 rows inserted in `settings` table.

- [ ] **Step 7: Smoke-test the model in tinker**

```bash
php artisan tinker
>>> App\Models\Setting::get('store_name')
// Expected: "Fenroy Supermarket"
>>> App\Models\Setting::set('store_name', 'Test')
>>> App\Models\Setting::get('store_name')
// Expected: "Test"
>>> App\Models\Setting::set('store_name', 'Fenroy Supermarket')
>>> exit
```

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_08_14_000001_create_settings_table.php database/seeders/SettingsSeeder.php app/Models/Setting.php database/seeders/DatabaseSeeder.php
git commit -m "feat: add settings key-value infrastructure"
```

---

### Task 2: OrderResource Bulk Actions + CSV Export Enhancement

**Files:**
- Modify: `app/Filament/Resources/OrderResource.php`
- Modify: `app/Filament/Resources/OrderResource/Pages/ListOrders.php`

**Interfaces:**
- Consumes: `Order` model with `status` field and pipeline values: `processing`, `picking`, `packed`, `out-for-delivery`, `delivered`, `cancelled`, `refunded`

- [ ] **Step 1: Add bulk actions to OrderResource**

Open `app/Filament/Resources/OrderResource.php`. Add these imports at the top with the existing imports:

```php
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Collection;
```

Note: `Select` and `Filament\Notifications\Notification` are already imported. Only add missing ones.

Replace the `->bulkActions([...])` section in the `table()` method (currently only has `DeleteBulkAction`) with:

```php
->bulkActions([
    BulkActionGroup::make([
        BulkAction::make('updateStatus')
            ->label('Update Status')
            ->icon('heroicon-m-arrow-path')
            ->form([
                Select::make('status')
                    ->label('New Status')
                    ->options([
                        'processing'       => 'Processing',
                        'picking'          => 'Picking',
                        'packed'           => 'Packed',
                        'out-for-delivery' => 'Out for Delivery',
                        'delivered'        => 'Delivered',
                        'cancelled'        => 'Cancelled',
                        'refunded'         => 'Refunded',
                    ])
                    ->required(),
            ])
            ->action(function (Collection $records, array $data): void {
                $records->each(fn ($record) => $record->update(['status' => $data['status']]));
                Notification::make()
                    ->title(count($records) . ' order(s) updated to ' . ucfirst(str_replace('-', ' ', $data['status'])))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion(),

        BulkAction::make('cancelOrders')
            ->label('Cancel Selected')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Cancel selected orders?')
            ->modalDescription('This will mark all selected orders as Cancelled.')
            ->action(function (Collection $records): void {
                $records->each(fn ($record) => $record->update(['status' => 'cancelled']));
                Notification::make()
                    ->title(count($records) . ' order(s) cancelled')
                    ->warning()
                    ->send();
            })
            ->deselectRecordsAfterCompletion(),

        BulkAction::make('refundOrders')
            ->label('Mark as Refunded')
            ->icon('heroicon-m-arrow-uturn-left')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Mark selected orders as Refunded?')
            ->modalDescription('This will mark all selected orders as Refunded.')
            ->action(function (Collection $records): void {
                $records->each(fn ($record) => $record->update(['status' => 'refunded']));
                Notification::make()
                    ->title(count($records) . ' order(s) marked as refunded')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion(),

        DeleteBulkAction::make(),
    ]),
])
```

- [ ] **Step 2: Enhance the CSV export with date range filter**

In `app/Filament/Resources/OrderResource/Pages/ListOrders.php`, replace the existing `exportCsv` action with a version that includes an optional date range form:

```php
<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->form([
                    DatePicker::make('from')->label('From (optional)'),
                    DatePicker::make('to')->label('To (optional)'),
                ])
                ->action(function (array $data) {
                    $query = Order::orderByDesc('created_at');

                    if (!empty($data['from'])) {
                        $query->whereDate('created_at', '>=', $data['from']);
                    }
                    if (!empty($data['to'])) {
                        $query->whereDate('created_at', '<=', $data['to']);
                    }

                    $orders = $query->get();

                    return response()->streamDownload(function () use ($orders) {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, [
                            'Order #', 'Customer', 'Phone', 'Email',
                            'Total (GH₵)', 'Delivery Fee (GH₵)', 'Discount (GH₵)',
                            'Status', 'Payment', 'Address', 'Notes', 'Date',
                        ]);
                        foreach ($orders as $o) {
                            fputcsv($handle, [
                                $o->order_number,
                                $o->customer_name,
                                $o->customer_phone,
                                $o->customer_email,
                                number_format($o->total, 2),
                                number_format($o->delivery_fee ?? 0, 2),
                                number_format($o->discount ?? 0, 2),
                                $o->status,
                                $o->payment_status,
                                $o->delivery_address,
                                $o->notes,
                                $o->created_at->format('Y-m-d H:i'),
                            ]);
                        }
                        fclose($handle);
                    }, 'orders-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
                }),
            CreateAction::make(),
        ];
    }
}
```

- [ ] **Step 3: Verify in browser**

Visit `/admin/orders`. Confirm:
- Checkbox column appears on the orders table
- Selecting rows shows bulk action dropdown with "Update Status", "Cancel Selected", "Mark as Refunded", "Delete"
- "Update Status" opens a modal with a status select, applying it updates the selected orders
- "Export CSV" header button opens a form with optional date range, downloading a CSV

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Resources/OrderResource.php app/Filament/Resources/OrderResource/Pages/ListOrders.php
git commit -m "feat: add order bulk actions (status, cancel, refund) and enhanced CSV export"
```

---

### Task 3: Coupon Analytics Columns

**Files:**
- Modify: `app/Filament/Resources/CouponResource.php`

**Interfaces:**
- Consumes: `Order` model with `coupon_code` (string) and `discount` (float) and `payment_status` fields
- Consumes: `Coupon` model with `code` field

- [ ] **Step 1: Add the DB import and computed column to CouponResource**

Open `app/Filament/Resources/CouponResource.php`. Add `use Illuminate\Support\Facades\DB;` to the imports.

In the `table()` method, add two new columns after the existing `used_count` column:

```php
TextColumn::make('used_count')
    ->label('Used / Limit')
    ->formatStateUsing(fn ($state, $record) =>
        $state . ($record->max_uses ? ' / ' . $record->max_uses : ' / ∞')
    ),

// ADD THESE TWO:
TextColumn::make('total_discount')
    ->label('Total Discount Given')
    ->getStateUsing(function ($record) {
        return DB::table('orders')
            ->where('coupon_code', $record->code)
            ->where('payment_status', 'paid')
            ->sum('discount');
    })
    ->formatStateUsing(fn ($state) => 'GH₵ ' . number_format((float) $state, 2))
    ->sortable(false),
```

- [ ] **Step 2: Verify in browser**

Visit `/admin/coupons`. Confirm a "Total Discount Given" column appears showing `GH₵ 0.00` for unused coupons and the correct sum for used ones.

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Resources/CouponResource.php
git commit -m "feat: add total discount given column to coupon list"
```

---

### Task 4: Customer Detail View (UserResource)

**Files:**
- Create: `app/Filament/Resources/UserResource/Pages/ViewUser.php`
- Modify: `app/Filament/Resources/UserResource.php`

**Interfaces:**
- Consumes: `Order` model queried by `customer_email`
- Consumes: `User` model with `name`, `email`, `phone`, `is_admin`, `created_at`

- [ ] **Step 1: Create the ViewUser page**

File: `app/Filament/Resources/UserResource/Pages/ViewUser.php`

```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;

class ViewUser extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.view-user';

    public User $record;

    public function mount(int|string $record): void
    {
        $this->record = User::findOrFail($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->record($this->record),
        ];
    }

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getOrdersHtml(): HtmlString
    {
        $orders = Order::where('customer_email', $this->record->email)
            ->orderByDesc('created_at')
            ->paginate(10);

        if ($orders->isEmpty()) {
            return new HtmlString('<p style="color:#6b7280;font-size:14px;padding:12px 0;">No orders yet.</p>');
        }

        $statusColor = [
            'processing'       => '#f59e0b',
            'picking'          => '#3b82f6',
            'packed'           => '#3b82f6',
            'out-for-delivery' => '#6366f1',
            'delivered'        => '#16a34a',
            'cancelled'        => '#ef4444',
            'refunded'         => '#6b7280',
        ];

        $rows = '';
        foreach ($orders as $o) {
            $color = $statusColor[$o->status] ?? '#6b7280';
            $payColor = $o->payment_status === 'paid' ? '#16a34a' : ($o->payment_status === 'failed' ? '#ef4444' : '#f59e0b');
            $rows .= '<tr>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;font-weight:600;">' . htmlspecialchars($o->order_number) . '</td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;"><span style="background:' . $color . '1a;color:' . $color . ';padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:600;">' . ucfirst(str_replace('-', ' ', $o->status)) . '</span></td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;"><span style="background:' . $payColor . '1a;color:' . $payColor . ';padding:2px 8px;border-radius:9999px;font-size:12px;font-weight:600;">' . ucfirst($o->payment_status) . '</span></td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;font-weight:600;">GH₵ ' . number_format($o->total, 2) . '</td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;color:#6b7280;">' . $o->created_at->format('d M Y, H:i') . '</td>'
                . '</tr>';
        }

        $html = '<table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">'
            . '<thead><tr style="background:#f9fafb;">'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Order #</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Status</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Payment</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Total</th>'
            . '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Date</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody></table>';

        return new HtmlString($html);
    }

    public function getStats(): array
    {
        $orders = Order::where('customer_email', $this->record->email);

        return [
            'total_orders' => $orders->count(),
            'total_spent'  => $orders->where('payment_status', 'paid')->sum('total'),
        ];
    }
}
```

- [ ] **Step 2: Create the Blade view**

Create directory `resources/views/filament/resources/user-resource/pages/` if it doesn't exist.

File: `resources/views/filament/resources/user-resource/pages/view-user.blade.php`

```blade
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
```

- [ ] **Step 3: Register the ViewUser page and add ViewAction to UserResource**

In `app/Filament/Resources/UserResource.php`:

Add to imports:
```php
use Filament\Actions\ViewAction;
```

Change `->actions([EditAction::make()])` in the table to:
```php
->actions([
    ViewAction::make(),
    EditAction::make(),
])
```

Change `getPages()` to:
```php
public static function getPages(): array
{
    return [
        'index' => Pages\ListUsers::route('/'),
        'view'  => Pages\ViewUser::route('/{record}'),
        'edit'  => Pages\EditUser::route('/{record}/edit'),
    ];
}
```

- [ ] **Step 4: Verify in browser**

Visit `/admin/users`. Click the View action on any user. Confirm:
- Profile card shows name, email, phone, joined date
- Two stat cards show total orders and total spent
- Order history table shows their orders (or "No orders yet.")
- Edit button in header takes you to the edit page

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Resources/UserResource.php app/Filament/Resources/UserResource/Pages/ViewUser.php resources/views/filament/resources/user-resource/pages/view-user.blade.php
git commit -m "feat: add customer detail view with order history and spending stats"
```

---

### Task 5: Product Performance Widget

**Files:**
- Create: `app/Filament/Widgets/ProductPerformanceTable.php`
- Modify: `app/Providers/Filament/AdminPanelProvider.php`

**Interfaces:**
- Consumes: `Order` model — `payment_status`, `created_at`, `items` (array of `{name, quantity, price}`)
- Produces: Dashboard widget at sort position 5, full width

- [ ] **Step 1: Create the widget**

File: `app/Filament/Widgets/ProductPerformanceTable.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ProductPerformanceTable extends BaseWidget
{
    protected static ?int $sort = 5;

    protected array|string|int $columnSpan = 'full';

    protected static ?string $heading = 'Top Products (Last 30 Days)';

    public static function canView(): bool
    {
        return Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();
    }

    private function getTopProducts(): Collection
    {
        $orders = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->get(['items']);

        $stats = [];
        foreach ($orders as $order) {
            foreach ((array) $order->items as $item) {
                $name = $item['name'] ?? 'Unknown';
                if (! isset($stats[$name])) {
                    $stats[$name] = ['name' => $name, 'units' => 0, 'revenue' => 0.0];
                }
                $qty = (int) ($item['quantity'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $stats[$name]['units'] += $qty;
                $stats[$name]['revenue'] += $qty * $price;
            }
        }

        usort($stats, fn ($a, $b) => $b['units'] <=> $a['units']);

        return collect(array_slice($stats, 0, 10))->values();
    }

    public function table(Table $table): Table
    {
        $products = $this->getTopProducts();

        return $table
            ->query(
                \App\Models\Product::query()->whereIn('name', $products->pluck('name')->toArray())
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->getStateUsing(function ($record) use ($products) {
                        $index = $products->search(fn ($p) => $p['name'] === $record->name);
                        return $index !== false ? $index + 1 : '—';
                    }),
                TextColumn::make('name')->label('Product')->searchable(),
                TextColumn::make('category')->label('Category')->placeholder('—'),
                TextColumn::make('units_sold')
                    ->label('Units Sold')
                    ->getStateUsing(function ($record) use ($products) {
                        $item = $products->firstWhere('name', $record->name);
                        return $item ? $item['units'] : 0;
                    })
                    ->sortable(false),
                TextColumn::make('revenue')
                    ->label('Revenue')
                    ->getStateUsing(function ($record) use ($products) {
                        $item = $products->firstWhere('name', $record->name);
                        return $item ? 'GH₵ ' . number_format($item['revenue'], 2) : 'GH₵ 0.00';
                    })
                    ->sortable(false),
            ])
            ->paginated(false)
            ->defaultSort('name');
    }
}
```

- [ ] **Step 2: Register the widget on the dashboard**

Open `app/Providers/Filament/AdminPanelProvider.php`. Find the `widgets([...])` or `->widgets([...])` call and add `ProductPerformanceTable::class` to it. Also add the import:

```php
use App\Filament\Widgets\ProductPerformanceTable;
```

Add `ProductPerformanceTable::class` alongside the existing widgets.

- [ ] **Step 3: Verify in browser**

Visit `/admin`. If there are any paid orders in the last 30 days, the "Top Products" widget appears at the bottom of the dashboard. Each row shows rank, product name, category, units sold, and revenue.

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Widgets/ProductPerformanceTable.php app/Providers/Filament/AdminPanelProvider.php
git commit -m "feat: add product performance dashboard widget (top 10 by units sold)"
```

---

### Task 6: Store Settings Page + Storefront Banner

**Files:**
- Create: `app/Filament/Pages/StoreSettings.php`
- Create: `resources/views/filament/pages/store-settings.blade.php`
- Modify: `resources/views/layouts/storefront.blade.php`

**Interfaces:**
- Consumes: `Setting::get()` and `Setting::set()` from Task 1
- Produces: `/admin/store-settings` page accessible from admin nav

- [ ] **Step 1: Create the StoreSettings page class**

File: `app/Filament/Pages/StoreSettings.php`

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

- [ ] **Step 2: Create the Blade view for StoreSettings**

File: `resources/views/filament/pages/store-settings.blade.php`

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

- [ ] **Step 3: Add announcement banner to storefront layout**

Open `resources/views/layouts/storefront.blade.php`. Add this block at the very top of `<body>`, before the desktop header `<header>` tag:

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

- [ ] **Step 4: Verify in browser**

1. Visit `/admin/store-settings` — form loads with current values from DB
2. Enable the banner, enter a message, click Save — notification appears
3. Visit the storefront `/` — red banner appears at the top with the message
4. Disable the banner, save — banner disappears from storefront

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Pages/StoreSettings.php resources/views/filament/pages/store-settings.blade.php resources/views/layouts/storefront.blade.php
git commit -m "feat: add store settings admin page with announcement banner"
```

---

### Task 7: Wishlist & Address Admin Resources

**Files:**
- Create: `app/Filament/Resources/WishlistResource.php`
- Create: `app/Filament/Resources/WishlistResource/Pages/ListWishlists.php`
- Create: `app/Filament/Resources/AddressResource.php`
- Create: `app/Filament/Resources/AddressResource/Pages/ListAddresses.php`

**Interfaces:**
- Consumes: `Wishlist` model with `user()` and `product()` relations
- Consumes: `Address` model with `user()` relation and fields: `full_name`, `phone`, `line1`, `city`, `region`, `is_default`

- [ ] **Step 1: Create WishlistResource**

File: `app/Filament/Resources/WishlistResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WishlistResource\Pages;
use App\Models\Wishlist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\User;

class WishlistResource extends Resource
{
    protected static ?string $model = Wishlist::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Wishlists';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Customer')
                    ->options(User::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWishlists::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
```

- [ ] **Step 2: Create ListWishlists page**

File: `app/Filament/Resources/WishlistResource/Pages/ListWishlists.php`

```php
<?php

namespace App\Filament\Resources\WishlistResource\Pages;

use App\Filament\Resources\WishlistResource;
use Filament\Resources\Pages\ListRecords;

class ListWishlists extends ListRecords
{
    protected static string $resource = WishlistResource::class;
}
```

- [ ] **Step 3: Create AddressResource**

File: `app/Filament/Resources/AddressResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Addresses';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')->label('Name')->searchable(),
                TextColumn::make('line1')->label('Street'),
                TextColumn::make('city')->label('City'),
                TextColumn::make('region')->label('Region')->placeholder('—'),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Customer')
                    ->options(User::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
```

- [ ] **Step 4: Create ListAddresses page**

File: `app/Filament/Resources/AddressResource/Pages/ListAddresses.php`

```php
<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Resources\Pages\ListRecords;

class ListAddresses extends ListRecords
{
    protected static string $resource = AddressResource::class;
}
```

- [ ] **Step 5: Verify in browser**

Visit `/admin`. Confirm "Wishlists" and "Addresses" appear in the Commerce nav group. Both list their data with the correct columns and user filter. No create/edit/delete buttons visible.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/WishlistResource.php app/Filament/Resources/WishlistResource/Pages/ListWishlists.php app/Filament/Resources/AddressResource.php app/Filament/Resources/AddressResource/Pages/ListAddresses.php
git commit -m "feat: add read-only Wishlist and Address admin resources"
```

---

### Task 8: Product Bulk Import

**Files:**
- Modify: `composer.json` (via `composer require`)
- Create: `app/Imports/ProductsImport.php`
- Create: `app/Filament/Pages/ImportProducts.php`
- Create: `resources/views/filament/pages/import-products.blade.php`
- Create: `public/templates/products-import-template.csv`

**Interfaces:**
- Consumes: `Product` model with `sku` as upsert key, `category` stored as string
- Consumes: `Category` model — validates that category names exist
- Produces: `/admin/import-products` Filament page

- [ ] **Step 1: Install maatwebsite/excel**

```bash
composer require maatwebsite/excel
```

Expected: package installed with no errors. Laravel auto-discovery registers the service provider.

- [ ] **Step 2: Create the template CSV**

Create directory `public/templates/` if it doesn't exist.

File: `public/templates/products-import-template.csv`

```
name,sku,unit,type,description,category,price,old_price,stock,is_active,is_featured,is_best_seller
Tomatoes (1kg),TOM-001,1kg pack,grocery,Fresh ripe tomatoes,Vegetables,5.99,,50,1,0,0
Chicken Breast,CHK-001,500g pack,grocery,Boneless chicken breast,Meat & Fish,25.00,28.00,30,1,1,0
```

- [ ] **Step 3: Create the ProductsImport class**

File: `app/Imports/ProductsImport.php`

```php
<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public int $created  = 0;
    public int $updated  = 0;
    public array $errors = [];

    private array $validCategories = [];

    public function __construct()
    {
        $this->validCategories = Category::pluck('name')
            ->map(fn ($n) => strtolower(trim($n)))
            ->toArray();
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            foreach (['name', 'sku', 'unit', 'price', 'stock', 'category'] as $field) {
                if (empty($row[$field]) && $row[$field] !== '0') {
                    $this->errors[] = "Row {$rowNum}: Missing required field '{$field}'";
                    continue 2;
                }
            }

            if (! in_array(strtolower(trim($row['category'])), $this->validCategories, true)) {
                $this->errors[] = "Row {$rowNum}: Unknown category '{$row['category']}'";
                continue;
            }

            if (! is_numeric($row['price'])) {
                $this->errors[] = "Row {$rowNum}: Invalid price '{$row['price']}'";
                continue;
            }

            if (! is_numeric($row['stock'])) {
                $this->errors[] = "Row {$rowNum}: Invalid stock '{$row['stock']}'";
                continue;
            }

            $data = [
                'name'           => trim($row['name']),
                'slug'           => Str::slug(trim($row['name'])),
                'unit'           => trim($row['unit']),
                'type'           => trim($row['type'] ?? 'grocery') ?: 'grocery',
                'description'    => trim($row['description'] ?? ''),
                'category'       => trim($row['category']),
                'price'          => (float) $row['price'],
                'old_price'      => (isset($row['old_price']) && is_numeric($row['old_price'])) ? (float) $row['old_price'] : null,
                'stock'          => (int) $row['stock'],
                'is_active'      => isset($row['is_active']) ? (bool)(int)$row['is_active'] : true,
                'is_featured'    => isset($row['is_featured']) ? (bool)(int)$row['is_featured'] : false,
                'is_best_seller' => isset($row['is_best_seller']) ? (bool)(int)$row['is_best_seller'] : false,
            ];

            $existing = Product::where('sku', trim($row['sku']))->first();

            if ($existing) {
                $existing->update($data);
                $this->updated++;
            } else {
                $data['sku'] = trim($row['sku']);
                Product::create($data);
                $this->created++;
            }
        }
    }
}
```

- [ ] **Step 4: Create the ImportProducts Filament page**

File: `app/Filament/Pages/ImportProducts.php`

```php
<?php

namespace App\Filament\Pages;

use App\Imports\ProductsImport;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;

class ImportProducts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Commerce';

    protected static ?string $navigationLabel = 'Import Products';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.import-products';

    public string $stage = 'upload'; // 'upload' | 'preview' | 'done'

    public ?string $tempPath = null;

    public array $previewRows = [];

    public array $previewHeaders = [];

    public int $totalRows = 0;

    public int $created = 0;

    public int $updated = 0;

    public array $importErrors = [];

    public function uploadFile(): void
    {
        $this->validate([
            'tempPath' => 'required',
        ], [
            'tempPath.required' => 'Please select a file to upload.',
        ]);

        $this->stage = 'preview';
    }

    public function handleUpload($event): void
    {
        if (! isset($event['filename'])) {
            return;
        }

        $tmpFile = storage_path('app/livewire-tmp/' . $event['filename']);

        if (! file_exists($tmpFile)) {
            return;
        }

        $storedName = 'imports/import-' . now()->format('YmdHis') . '-' . $event['filename'];
        $storedPath = storage_path('app/' . $storedName);

        if (! is_dir(dirname($storedPath))) {
            mkdir(dirname($storedPath), 0755, true);
        }

        copy($tmpFile, $storedPath);
        $this->tempPath = $storedPath;

        // Build preview
        $reader = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\ProductsImport(), $storedPath, null, \Maatwebsite\Excel\Excel::CSV);

        if ($reader->isNotEmpty() && $reader->first()->isNotEmpty()) {
            $allRows = $reader->first();
            $this->totalRows = max(0, count($allRows) - 1); // subtract header
            // Get first row as headers (WithHeadingRow means first data row is index 0)
        }

        // For preview we read raw (first 6 rows including header)
        $handle = fopen($storedPath, 'r');
        $this->previewHeaders = fgetcsv($handle) ?: [];
        $previewData = [];
        $count = 0;
        while (($row = fgetcsv($handle)) !== false && $count < 5) {
            $previewData[] = $row;
            $count++;
        }
        fclose($handle);
        $this->previewRows = $previewData;
        $this->totalRows   = $count; // approximate; full count done on import

        $this->stage = 'preview';
    }

    public function runImport(): void
    {
        if (! $this->tempPath || ! file_exists($this->tempPath)) {
            Notification::make()->title('Import file not found. Please re-upload.')->danger()->send();
            $this->stage = 'upload';
            return;
        }

        $import = new ProductsImport();

        $ext = strtolower(pathinfo($this->tempPath, PATHINFO_EXTENSION));
        $type = $ext === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::CSV;

        Excel::import($import, $this->tempPath, null, $type);

        $this->created      = $import->created;
        $this->updated      = $import->updated;
        $this->importErrors = $import->errors;

        @unlink($this->tempPath);
        $this->tempPath = null;

        $this->stage = 'done';

        Notification::make()
            ->title("Import complete: {$this->created} created, {$this->updated} updated" . (count($this->importErrors) ? ', ' . count($this->importErrors) . ' errors' : ''))
            ->success()
            ->send();
    }

    public function resetImport(): void
    {
        $this->stage        = 'upload';
        $this->tempPath     = null;
        $this->previewRows  = [];
        $this->previewHeaders = [];
        $this->totalRows    = 0;
        $this->created      = 0;
        $this->updated      = 0;
        $this->importErrors = [];
    }
}
```

- [ ] **Step 5: Create the import Blade view**

File: `resources/views/filament/pages/import-products.blade.php`

```blade
<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Template download --}}
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <p style="font-size:14px;font-weight:600;color:#15803d;">Download Template</p>
                <p style="font-size:13px;color:#166534;margin-top:2px;">Use this CSV template to prepare your product data correctly.</p>
            </div>
            <a href="/templates/products-import-template.csv" download
               style="padding:8px 16px;background:#16a34a;color:white;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                Download Template
            </a>
        </div>

        {{-- STAGE: Upload --}}
        @if($stage === 'upload')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Upload Product File</h2>
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Accepted formats: <strong>CSV</strong> or <strong>Excel (.xlsx)</strong>. Max file size: 5MB. Products are matched by SKU — existing products will be updated.</p>

            <div x-data="{
                    file: null,
                    uploading: false,
                    handleFile(event) {
                        this.file = event.target.files[0];
                    },
                    async upload() {
                        if (!this.file) return;
                        this.uploading = true;
                        const formData = new FormData();
                        formData.append('file', this.file);
                        // Store in livewire tmp
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            $wire.call('handleUpload', { filename: this.file.name, content: e.target.result });
                            this.uploading = false;
                        };
                        reader.readAsDataURL(this.file);
                    }
                }">
                <input type="file" accept=".csv,.xlsx"
                    x-on:change="handleFile"
                    style="display:block;width:100%;padding:10px;border:2px dashed #d1d5db;border-radius:8px;font-size:14px;cursor:pointer;background:#fafafa;">
                <p x-show="file" x-text="'Selected: ' + (file ? file.name : '')" style="font-size:13px;color:#374151;margin-top:8px;"></p>
            </div>

            {{-- Simple file upload via Livewire --}}
            <div style="margin-top:16px;">
                <form wire:submit.prevent="uploadFile">
                    <livewire:components.file-upload-input wire:model="tempPath" accept=".csv,.xlsx" />
                    <p style="font-size:12px;color:#6b7280;margin-top:8px;">Select a CSV or Excel file, then click Preview.</p>
                </form>
            </div>

            <div style="margin-top:20px;">
                <input type="file" accept=".csv,.xlsx" id="import-file-input"
                    style="display:block;width:100%;padding:10px;border:2px dashed #d1d5db;border-radius:8px;font-size:14px;cursor:pointer;background:#fafafa;"
                    x-data
                    x-on:change="
                        const file = $event.target.files[0];
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const base64 = e.target.result.split(',')[1];
                            const ext = file.name.split('.').pop();
                            fetch('/admin/import-products/upload', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                                body: JSON.stringify({file: base64, name: file.name, ext: ext})
                            }).then(r => r.json()).then(d => {
                                if (d.path) { $wire.set('tempPath', d.path); }
                            });
                        };
                        reader.readAsDataURL(file);
                    ">
            </div>

        </div>
        @endif

        {{-- STAGE: Preview --}}
        @if($stage === 'preview')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Preview (first {{ count($previewRows) }} rows)</h2>
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Review the data below, then click Import to process the file.</p>

            @if(count($previewRows) > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            @foreach($previewHeaders as $header)
                            <th style="padding:8px 12px;text-align:left;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;font-size:11px;white-space:nowrap;">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewRows as $row)
                        <tr>
                            @foreach($row as $cell)
                            <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;color:#374151;white-space:nowrap;">{{ $cell }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p style="color:#ef4444;font-size:13px;">No data rows found in the file.</p>
            @endif

            <div style="margin-top:20px;display:flex;gap:12px;">
                <button wire:click="runImport" style="padding:10px 20px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Confirm Import
                </button>
                <button wire:click="resetImport" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Cancel
                </button>
            </div>
        </div>
        @endif

        {{-- STAGE: Done --}}
        @if($stage === 'done')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Import Complete</h2>

            <div style="display:flex;gap:16px;margin-bottom:20px;">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#15803d;">{{ $created }}</p>
                    <p style="font-size:13px;color:#166534;margin-top:4px;">Products Created</p>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#1d4ed8;">{{ $updated }}</p>
                    <p style="font-size:13px;color:#1e40af;margin-top:4px;">Products Updated</p>
                </div>
                @if(count($importErrors) > 0)
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#b91c1c;">{{ count($importErrors) }}</p>
                    <p style="font-size:13px;color:#991b1b;margin-top:4px;">Rows Skipped</p>
                </div>
                @endif
            </div>

            @if(count($importErrors) > 0)
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin-bottom:20px;">
                <p style="font-size:13px;font-weight:600;color:#b91c1c;margin-bottom:8px;">Skipped rows:</p>
                <ul style="list-style:disc;padding-left:20px;font-size:13px;color:#991b1b;space-y:4px;">
                    @foreach($importErrors as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div style="display:flex;gap:12px;">
                <button wire:click="resetImport" style="padding:10px 20px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Import Another File
                </button>
                <a href="/admin/products" style="padding:10px 20px;background:#f3f4f6;color:#374151;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;">
                    View Products
                </a>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
```

- [ ] **Step 6: Add file upload route and controller action**

The import page needs to receive file uploads. Add a route and handle the upload. Open `routes/web.php` and add:

```php
use Illuminate\Http\Request;

Route::post('/admin/import-products/upload', function (Request $request) {
    $request->validate(['file' => 'required|string', 'name' => 'required|string', 'ext' => 'required|in:csv,xlsx']);

    $content = base64_decode($request->file);
    $filename = 'imports/import-' . now()->format('YmdHis') . '.' . $request->ext;
    $path = storage_path('app/' . $filename);

    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    file_put_contents($path, $content);

    return response()->json(['path' => $path]);
})->middleware(['auth'])->name('admin.import.upload');
```

**Security note:** The `auth` middleware ensures only logged-in users can upload. The base64-decoded content is written to a private storage path (not public).

Update `ImportProducts.php` to simplify — remove the broken x-data approach from the Blade view and replace it with a clean Livewire file upload. Update the Blade view to use a simple standard HTML file input that triggers the upload route, then sets `$wire.set('tempPath', path)` and calls `$wire.call('goToPreview')`.

Add this method to `ImportProducts.php`:

```php
public function goToPreview(): void
{
    if (! $this->tempPath || ! file_exists($this->tempPath)) {
        Notification::make()->title('File not found. Please re-upload.')->danger()->send();
        return;
    }

    $ext = strtolower(pathinfo($this->tempPath, PATHINFO_EXTENSION));

    $handle = fopen($this->tempPath, 'r');
    $this->previewHeaders = fgetcsv($handle) ?: [];
    $previewData = [];
    $count = 0;
    while (($row = fgetcsv($handle)) !== false && $count < 5) {
        $previewData[] = $row;
        $count++;
    }
    fclose($handle);
    $this->previewRows = $previewData;

    $this->stage = 'preview';
}
```

Replace the upload stage HTML in the Blade with:

```blade
{{-- STAGE: Upload --}}
@if($stage === 'upload')
<div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
    <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Upload Product File</h2>
    <p style="font-size:13px;color:#6b7280;margin-bottom:20px;">Accepted: <strong>CSV</strong> or <strong>Excel (.xlsx)</strong>. Max 5MB. Existing products matched by SKU will be updated.</p>

    <div x-data="{ uploading: false, selectedName: '' }">
        <input type="file" accept=".csv,.xlsx"
            style="display:block;width:100%;padding:10px;border:2px dashed #d1d5db;border-radius:8px;font-size:14px;cursor:pointer;background:#fafafa;"
            x-on:change="
                const file = $event.target.files[0];
                if (!file) return;
                selectedName = file.name;
                uploading = true;
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const base64 = e.target.result.split(',')[1];
                    const ext = file.name.split('.').pop().toLowerCase();
                    const resp = await fetch('/admin/import-products/upload', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({ file: base64, name: file.name, ext })
                    });
                    const data = await resp.json();
                    uploading = false;
                    if (data.path) {
                        await $wire.set('tempPath', data.path);
                        await $wire.call('goToPreview');
                    }
                };
                reader.readAsDataURL(file);
            ">
        <p x-show="selectedName" x-text="uploading ? 'Uploading ' + selectedName + '...' : ''" style="font-size:13px;color:#374151;margin-top:8px;"></p>
    </div>
</div>
@endif
```

- [ ] **Step 7: Verify in browser**

1. Visit `/admin/import-products`
2. Download the template CSV — opens correct file with 2 sample rows
3. Fill in some product data and upload the file
4. Preview screen shows first 5 rows with correct headers
5. Click "Confirm Import" — results screen shows created/updated counts
6. Any rows with missing required fields or unknown categories appear in the errors list
7. Visit `/admin/products` — imported products appear

- [ ] **Step 8: Commit**

```bash
git add app/Imports/ProductsImport.php app/Filament/Pages/ImportProducts.php resources/views/filament/pages/import-products.blade.php public/templates/products-import-template.csv routes/web.php
git commit -m "feat: add product bulk import (CSV/Excel) with preview and error reporting"
```

---

## Self-Review

**Spec coverage check:**

| Spec requirement | Task |
|---|---|
| Bulk order status update | Task 2 |
| Order CSV export with date range | Task 2 |
| Cancel / Refunded bulk actions | Task 2 |
| Coupon total discount column | Task 3 |
| Customer detail view (orders + stats) | Task 4 |
| Product performance widget | Task 5 |
| Settings table + model + seeder | Task 1 |
| StoreSettings page (all fields) | Task 6 |
| Announcement banner on storefront | Task 6 |
| WishlistResource (read-only) | Task 7 |
| AddressResource (read-only) | Task 7 |
| Product bulk import (CSV/Excel, preview, errors) | Task 8 |
| Template download CSV | Task 8 |

**All spec items covered.**

**Type consistency:**
- `Setting::get()` defined in Task 1, consumed in Task 6 ✓
- `Setting::set()` defined in Task 1, consumed in Task 6 ✓
- `ProductsImport->created`, `->updated`, `->errors` defined in Task 8 step 3, consumed in step 4 ✓
- `BulkAction` used consistently with `Filament\Actions\BulkActionGroup` namespace from existing code ✓

**No placeholders found.**
