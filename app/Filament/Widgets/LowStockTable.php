<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected array|string|int $columnSpan = 'full';

    protected static ?string $heading = 'Low Stock Alert (≤ 5 units)';

    public static function canView(): bool
    {
        return Product::where('stock', '<=', 5)->where('is_active', true)->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->where('stock', '<=', 5)->where('is_active', true)->orderBy('stock'))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('sku')->label('SKU')->placeholder('—'),
                TextColumn::make('category'),
                TextColumn::make('stock')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning'),
            ])
            ->paginated(false);
    }
}
