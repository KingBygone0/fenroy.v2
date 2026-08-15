<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $counts = Order::query()
            ->select('customer_email', DB::raw('COUNT(*) as order_count'))
            ->where('payment_status', 'paid')
            ->groupBy('customer_email')
            ->get();

        $total     = $counts->count();
        $new       = $counts->where('order_count', 1)->count();
        $returning = $counts->where('order_count', '>', 1)->count();
        $returnPct = $total > 0 ? round($returning / $total * 100) : 0;

        return [
            Stat::make('New Customers', $new)
                ->description('Placed exactly 1 order')
                ->color('info')
                ->icon('heroicon-o-user-plus'),

            Stat::make('Returning Customers', $returning)
                ->description("{$returnPct}% of all buyers")
                ->color('success')
                ->icon('heroicon-o-arrow-path'),
        ];
    }
}
