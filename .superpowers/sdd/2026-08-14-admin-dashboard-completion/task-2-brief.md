# Task 2 Brief: OrderResource Bulk Actions + CSV Export Enhancement

## Context
Task 2 of 8. Laravel 13 / Filament v5 project at C:\Users\King\Documents\LYNJAY\laravel\fenroy.

The existing `OrderResource.php` already has a single `DeleteBulkAction` in its bulk actions. The existing `ListOrders.php` already has an `exportCsv` action (exports all orders, no date filter). You are REPLACING/ENHANCING these.

## What to do

### 1. Modify `app/Filament/Resources/OrderResource.php`

Add three new bulk actions alongside `DeleteBulkAction`. The existing imports include `Filament\Actions\BulkActionGroup` and `Filament\Actions\DeleteBulkAction`.

**Important:** `BulkAction` (the custom one) in Filament v5 may be at `Filament\Tables\Actions\BulkAction`. Try that first. If it doesn't exist, try `Filament\Actions\BulkAction`.

Also add `use Illuminate\Database\Eloquent\Collection;` to imports (for the closure type hint).

Replace the `->bulkActions([...])` call (currently just `DeleteBulkAction`) with:

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

Note: `Select` is already imported in `OrderResource.php`. `Notification` is already imported. Only add what's missing.

### 2. Fully replace `app/Filament/Resources/OrderResource/Pages/ListOrders.php`

Write the entire file with the enhanced CSV export (date range form):

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

## Verification
Run `php artisan route:list | grep admin` to confirm no route errors. Run `php artisan config:clear && php artisan view:clear` to clear caches.

Check for PHP syntax errors: `php artisan tinker --execute="echo 'ok'"`

## Commit
```bash
git add app/Filament/Resources/OrderResource.php app/Filament/Resources/OrderResource/Pages/ListOrders.php
git commit -m "feat: add order bulk actions (status, cancel, refund) and enhanced CSV export"
```

## Report file
Write report to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-2-report.md`

Return: "Status: DONE, commit: [hash]" or "Status: BLOCKED, reason: [reason]"
