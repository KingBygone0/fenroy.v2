# Task 4 Brief: Customer Detail View (UserResource)

## Context
Task 4 of 8. Laravel 13 / Filament v5 at C:\Users\King\Documents\LYNJAY\laravel\fenroy.

Key facts:
- Orders link to users by `customer_email` field (no user_id FK on orders)
- User model fields: name, email, phone, is_admin, created_at
- `Address` model has: user_id, full_name, phone, line1, city, region, is_default
- Existing UserResource only has List and Edit pages

## Files to create/modify

### 1. Create `app/Filament/Resources/UserResource/Pages/ViewUser.php`

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
        $baseQuery = Order::where('customer_email', $this->record->email);

        return [
            'total_orders' => $baseQuery->count(),
            'total_spent'  => (clone $baseQuery)->where('payment_status', 'paid')->sum('total'),
        ];
    }
}
```

### 2. Create `resources/views/filament/resources/user-resource/pages/view-user.blade.php`

Create the directory first if needed. File content:

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

### 3. Modify `app/Filament/Resources/UserResource.php`

Read the existing file first, then:
- Add `use Filament\Actions\ViewAction;` to imports
- Change `->actions([EditAction::make()])` to:
  ```php
  ->actions([
      ViewAction::make(),
      EditAction::make(),
  ])
  ```
- Change `getPages()` to:
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

## IMPORTANT NOTE on getStats()
The original plan has a bug: it creates `$orders = Order::where(...)` as a Builder then calls `$orders->count()` then `$orders->where('payment_status', 'paid')->sum('total')` — the second where() modifies the same builder after count() already ran. Use `clone` or separate queries:

```php
public function getStats(): array
{
    $baseQuery = Order::where('customer_email', $this->record->email);

    return [
        'total_orders' => $baseQuery->count(),
        'total_spent'  => (clone $baseQuery)->where('payment_status', 'paid')->sum('total'),
    ];
}
```

## Verify
`php -l app/Filament/Resources/UserResource.php`
`php -l app/Filament/Resources/UserResource/Pages/ViewUser.php`

## Commit
```bash
git add app/Filament/Resources/UserResource.php app/Filament/Resources/UserResource/Pages/ViewUser.php resources/views/filament/resources/user-resource/pages/view-user.blade.php
git commit -m "feat: add customer detail view with order history and spending stats"
```

## Report
Write to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-4-report.md`
Return: "Status: DONE, commit: [hash]" or "Status: BLOCKED, reason: [reason]"
