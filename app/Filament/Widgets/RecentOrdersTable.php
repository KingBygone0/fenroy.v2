<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected array|string|int $columnSpan = 'full';

    protected static ?string $heading = 'Recent Orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(10))
            ->columns([
                TextColumn::make('order_number')->weight('bold')->copyable(),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('customer_phone'),
                TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => 'GH₵ ' . number_format($state, 2)),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'processing'       => 'warning',
                        'picking'          => 'info',
                        'packed'           => 'info',
                        'out-for-delivery' => 'primary',
                        'delivered'        => 'success',
                        'cancelled'        => 'danger',
                        default            => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'out-for-delivery' => 'Out for Delivery',
                        default            => ucfirst($state),
                    }),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'  => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')->since()->label('Placed'),
            ])
            ->paginated(false);
    }
}
