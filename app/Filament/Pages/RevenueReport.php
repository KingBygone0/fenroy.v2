<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;

class RevenueReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Revenue Report';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.revenue-report';

    public string $dateFrom = '';
    public string $dateTo   = '';
    public string $groupBy  = 'day';

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public function getData(): \Illuminate\Support\Collection
    {
        $from = $this->dateFrom . ' 00:00:00';
        $to   = $this->dateTo . ' 23:59:59';

        $base = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to]);

        return match ($this->groupBy) {
            'week' => $base
                ->selectRaw('DATE_FORMAT(MIN(created_at), "%Y W%u") as period, COUNT(*) as orders, SUM(total) as revenue')
                ->groupByRaw('YEARWEEK(created_at, 1)')
                ->orderByRaw('YEARWEEK(created_at, 1) DESC')
                ->get(),
            'month' => $base
                ->selectRaw('DATE_FORMAT(created_at, "%Y %b") as period, COUNT(*) as orders, SUM(total) as revenue')
                ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m") DESC')
                ->get(),
            default => $base
                ->selectRaw('DATE(created_at) as period, COUNT(*) as orders, SUM(total) as revenue')
                ->groupByRaw('DATE(created_at)')
                ->orderBy('period', 'desc')
                ->get(),
        };
    }

    public function getTotals(): array
    {
        $result = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->selectRaw('COUNT(*) as orders, COALESCE(SUM(total), 0) as revenue')
            ->first();

        return [
            'orders'  => $result->orders ?? 0,
            'revenue' => $result->revenue ?? 0,
        ];
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = $this->getData();

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Period', 'Orders', 'Revenue (GH₵)']);
            foreach ($data as $row) {
                fputcsv($handle, [$row->period, $row->orders, number_format($row->revenue, 2)]);
            }
            fclose($handle);
        }, 'revenue-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
