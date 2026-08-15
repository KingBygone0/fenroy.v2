<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreOverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected array|string|int $columnSpan = 'full';

    protected function getStats(): array
    {
        $todayRevenue  = Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total');
        $todayOrders   = Order::whereDate('created_at', today())->where('payment_status', 'paid')->count();
        $pendingOrders = Order::whereIn('status', ['processing', 'picking', 'packed'])->count();
        $totalOrders   = Order::count();
        $totalRevenue  = Order::where('payment_status', 'paid')->sum('total');

        $yesterdayRevenue = Order::whereDate('created_at', today()->subDay())->where('payment_status', 'paid')->sum('total');
        $revenueChange    = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100)
            : ($todayRevenue > 0 ? 100 : 0);

        return [
            Stat::make("Today's Revenue", 'GH₵ ' . number_format($todayRevenue, 2))
                ->description($revenueChange >= 0
                    ? "{$revenueChange}% increase from yesterday"
                    : abs($revenueChange) . '% decrease from yesterday')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make("Today's Orders", $todayOrders)
                ->description("{$pendingOrders} pending fulfilment")
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('All-time Revenue', 'GH₵ ' . number_format($totalRevenue, 2))
                ->description("{$totalOrders} total orders")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Pending Fulfilment', $pendingOrders)
                ->description('Processing + picking + packed')
                ->descriptionIcon('heroicon-m-truck')
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),
        ];
    }
}
