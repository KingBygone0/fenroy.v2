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
        $names    = $products->pluck('name')->toArray();

        // Build FIELD() placeholders to preserve rank sort order from the aggregation
        $placeholders = implode(',', array_fill(0, count($names), '?'));

        return $table
            ->query(
                Product::query()
                    ->whereIn('name', $names)
                    ->when(
                        count($names) > 0,
                        fn ($q) => $q->orderByRaw("FIELD(name, {$placeholders})", $names)
                    )
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
            ->paginated(false);
    }
}
