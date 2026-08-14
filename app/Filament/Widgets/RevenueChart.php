<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue — Last 7 Days (GH₵)';

    protected array|string|int $columnSpan = 'full';

    protected function getData(): array
    {
        $points = collect(range(6, 0))->map(function (int $daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label'   => $date->format('D d M'),
                'revenue' => (float) Order::whereDate('created_at', $date)
                    ->where('payment_status', 'paid')
                    ->sum('total'),
            ];
        });

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (GH₵)',
                    'data'            => $points->pluck('revenue')->toArray(),
                    'borderColor'     => '#E53935',
                    'backgroundColor' => 'rgba(229,57,53,0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $points->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
