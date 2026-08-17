<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function refresh(): void
    {
        // called by wire:poll — render() will recompute count automatically
    }

    public function render()
    {
        $count = Order::where('is_walk_in', false)
            ->where('status', 'pending')
            ->count();

        $recent = Order::where('is_walk_in', false)
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get(['id', 'order_number', 'customer_name', 'total', 'created_at']);

        return view('livewire.pos.notification-bell', compact('count', 'recent'));
    }
}
