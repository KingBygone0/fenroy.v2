<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;

class RevenueReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Revenue Report';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.revenue-report';

    #[Rule('required|date_format:Y-m-d')]
    public string $dateFrom = '';

    #[Rule('required|date_format:Y-m-d|after_or_equal:dateFrom')]
    public string $dateTo = '';

    #[Rule('required|in:day,week,month')]
    public string $groupBy = 'day';

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    #[Computed]
    public function reportData(): \Illuminate\Support\Collection
    {
        // Guard against malformed dates before they reach the DB
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dateFrom) ||
            ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dateTo)) {
            return collect();
        }

        $from = $this->dateFrom . ' 00:00:00';
        $to   = $this->dateTo . ' 23:59:59';

        $base = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to]);

        return match ($this->groupBy) {
            'week' => $base
                ->selectRaw('DATE_FORMAT(MIN(created_at), "%Y W%u") as period, COUNT(*) as orders, SUM(total) as revenue, COALESCE(SUM(delivery_fee), 0) as delivery_total')
                ->groupByRaw('YEARWEEK(created_at, 1)')
                ->orderByRaw('YEARWEEK(created_at, 1) DESC')
                ->get(),
            'month' => $base
                ->selectRaw('DATE_FORMAT(created_at, "%Y %b") as period, COUNT(*) as orders, SUM(total) as revenue, COALESCE(SUM(delivery_fee), 0) as delivery_total')
                ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m") DESC')
                ->get(),
            default => $base
                ->selectRaw('DATE(created_at) as period, COUNT(*) as orders, SUM(total) as revenue, COALESCE(SUM(delivery_fee), 0) as delivery_total')
                ->groupByRaw('DATE(created_at)')
                ->orderBy('period', 'desc')
                ->get(),
        };
    }

    public function getTotals(): array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dateFrom) ||
            ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dateTo)) {
            return ['orders' => 0, 'revenue' => 0, 'net_revenue' => 0, 'delivery' => 0, 'avg_order' => 0, 'prev_revenue' => 0, 'change' => 0];
        }

        $from = Carbon::parse($this->dateFrom);
        $to   = Carbon::parse($this->dateTo);
        $days = max(1, $from->diffInDays($to) + 1);

        $result = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue, COALESCE(SUM(delivery_fee), 0) as delivery_total')
            ->first();

        $orders   = (int)   ($result->orders        ?? 0);
        $revenue  = (float) ($result->revenue        ?? 0);
        $delivery = (float) ($result->delivery_total ?? 0);

        $prevResult = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [
                $from->copy()->subDays($days)->format('Y-m-d') . ' 00:00:00',
                $from->copy()->subDay()->format('Y-m-d')       . ' 23:59:59',
            ])
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->first();

        $prevRevenue = (float) ($prevResult->revenue ?? 0);
        $change = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : ($revenue > 0 ? 100.0 : 0.0);

        return [
            'orders'      => $orders,
            'revenue'     => $revenue,
            'net_revenue' => $revenue - $delivery,
            'delivery'    => $delivery,
            'avg_order'   => $orders > 0 ? $revenue / $orders : 0.0,
            'prev_revenue'=> $prevRevenue,
            'change'      => $change,
        ];
    }

    public function getChartData(): array
    {
        // Reverse so chart reads oldest → newest (left → right)
        $data = $this->reportData->reverse()->values();

        return [
            'labels'   => $data->pluck('period')->toArray(),
            'revenues' => $data->pluck('revenue')->map(fn ($v) => round((float) $v, 2))->toArray(),
        ];
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = $this->reportData;

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Period', 'Orders', 'Revenue (GH₵)', 'Delivery (GH₵)', 'Net Revenue (GH₵)']);
            foreach ($data as $row) {
                $net = (float) $row->revenue - (float) ($row->delivery_total ?? 0);
                fputcsv($handle, [
                    $row->period,
                    $row->orders,
                    number_format($row->revenue, 2),
                    number_format($row->delivery_total ?? 0, 2),
                    number_format($net, 2),
                ]);
            }
            fclose($handle);
        }, 'revenue-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
