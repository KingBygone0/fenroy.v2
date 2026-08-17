<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use Livewire\Component;

class Reports extends Component
{
    public string $period = 'today';

    private function baseQuery(string $period = null)
    {
        $p = $period ?? $this->period;
        return Order::where('payment_status', 'paid')
            ->when($p === 'today', fn ($q) => $q->whereDate('created_at', today()))
            ->when($p === 'week',  fn ($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($p === 'month', fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year));
    }

    public function render()
    {
        $stats = [
            'walk_in_revenue' => (clone $this->baseQuery())->where('is_walk_in', true)->sum('total'),
            'online_revenue'  => (clone $this->baseQuery())->where('is_walk_in', false)->sum('total'),
            'walk_in_orders'  => (clone $this->baseQuery())->where('is_walk_in', true)->count(),
            'online_orders'   => (clone $this->baseQuery())->where('is_walk_in', false)->count(),
            'total_revenue'   => (clone $this->baseQuery())->sum('total'),
            'total_orders'    => (clone $this->baseQuery())->count(),
        ];

        $topProducts = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get(['items'])
            ->flatMap(function ($order) {
                $items = is_array($order->items) ? $order->items : json_decode($order->items, true);
                return collect($items ?? []);
            })
            ->groupBy('name')
            ->map(fn ($items, $name) => [
                'name'    => $name,
                'qty'     => collect($items)->sum('qty'),
                'revenue' => collect($items)->sum(fn ($i) => ($i['price'] ?? 0) * ($i['qty'] ?? 1)),
            ])
            ->sortByDesc('qty')
            ->take(10)
            ->values();

        return view('livewire.pos.reports', compact('stats', 'topProducts'))
            ->layout('layouts.pos');
    }
}
