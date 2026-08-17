<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Customers extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function render()
    {
        $customers = DB::table('orders')
            ->select(
                'customer_email',
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('MAX(customer_phone) as customer_phone'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('MAX(created_at) as last_order_at')
            )
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->when($this->search, fn ($q) =>
                $q->where('customer_name', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_email', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_phone', 'like', '%'.$this->search.'%')
            )
            ->groupBy('customer_email')
            ->orderByDesc('last_order_at')
            ->paginate(25);

        return view('livewire.pos.customers', compact('customers'))
            ->layout('layouts.pos');
    }
}
