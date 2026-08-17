# Task 5 Brief: Product Performance Widget

## Context
Task 5 of 8. Laravel 13 / Filament v5 at C:\Users\King\Documents\LYNJAY\laravel\fenroy.

**Key facts:**
- Order `items` is a JSON array, each element: `{name: string, quantity: int, price: float}` — NO product_id
- `Product::category` is stored as a plain string
- Filament auto-discovers widgets from `app/Filament/Widgets/` — no need to touch `AdminPanelProvider.php`
- Existing widgets: StoreOverviewStats (sort=1), RecentOrdersTable (sort=2), RevenueChart (sort=3), LowStockTable (sort=4)
- This widget: sort=5

## Create `app/Filament/Widgets/ProductPerformanceTable.php`

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;

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
                $qty   = (int) ($item['quantity'] ?? 0);
                $price = (float) ($item['price'] ?? 0);
                $stats[$name]['units']   += $qty;
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
                Product::query()->whereIn('name', $products->pluck('name')->toArray())
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

## Verify
`php -l app/Filament/Widgets/ProductPerformanceTable.php`

## Commit
```bash
git add app/Filament/Widgets/ProductPerformanceTable.php
git commit -m "feat: add product performance dashboard widget (top 10 by units sold)"
```

## Report
Write to: `.superpowers/sdd/2026-08-14-admin-dashboard-completion/task-5-report.md`
Return: "Status: DONE, commit: [hash]" or "Status: BLOCKED, reason: [reason]"
