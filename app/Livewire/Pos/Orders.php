<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $dateFilter = 'today';
    public string $tab        = 'walkin';

    public function mount(): void
    {
        if (request('tab') === 'online') {
            $this->tab = 'online';
        }
    }

    public function updatedSearch(): void     { $this->resetPage(); }
    public function updatedDateFilter(): void { $this->resetPage(); }
    public function updatedTab(): void        { $this->resetPage(); }

    public function acceptOrder(int $orderId): void
    {
        Order::where('id', $orderId)
             ->where('is_walk_in', false)
             ->update(['status' => 'processing']);
    }

    public function render()
    {
        $isWalkIn = $this->tab === 'walkin';

        $orders = Order::where('is_walk_in', $isWalkIn)
            ->when($this->search, fn ($q) =>
                $q->where('order_number', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_name',  'like', '%'.$this->search.'%')
                  ->orWhere('customer_phone', 'like', '%'.$this->search.'%')
            )
            ->when($this->dateFilter === 'today', fn ($q) => $q->whereDate('created_at', today()))
            ->when($this->dateFilter === 'week',  fn ($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->dateFilter === 'month', fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->orderByDesc('created_at')
            ->paginate(20);

        $totalRevenue = Order::where('is_walk_in', $isWalkIn)
            ->when($this->dateFilter === 'today', fn ($q) => $q->whereDate('created_at', today()))
            ->when($this->dateFilter === 'week',  fn ($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->dateFilter === 'month', fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->sum('total');

        $pendingOnlineCount = Order::where('is_walk_in', false)
            ->where('status', 'pending')
            ->count();

        return view('livewire.pos.orders', compact('orders', 'totalRevenue', 'pendingOnlineCount'))
            ->layout('layouts.pos');
    }
}
