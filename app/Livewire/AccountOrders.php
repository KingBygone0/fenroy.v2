<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use Livewire\Component;

class AccountOrders extends Component
{
    public string $tab = 'all';

    public function switchTab(string $tab): void
    {
        $allowed = ['all', 'processing', 'delivered', 'cancelled'];
        $this->tab = in_array($tab, $allowed, true) ? $tab : 'all';
    }

    public function reorder(int $index): void
    {
        $orders = $this->getFilteredOrders();
        $order  = $orders[$index] ?? null;
        if (! $order) return;

        // Re-fetch current prices from DB — never trust historical order prices
        $slugs    = array_filter(array_column($order['items'], 'slug'));
        $products = Product::whereIn('slug', $slugs)->where('is_active', true)
            ->get()->keyBy('slug');

        $cartItems = session('cart_items', []);
        foreach ($order['items'] as $item) {
            $slug      = $item['slug'] ?? '';
            $dbProduct = $products->get($slug);
            if (! $dbProduct) continue;

            $found = false;
            foreach ($cartItems as &$ci) {
                if ($ci['slug'] === $slug) {
                    $ci['qty']  += $item['qty'] ?? 1;
                    $ci['price'] = $dbProduct->price;
                    $found = true;
                    break;
                }
            }
            unset($ci);
            if (! $found) {
                $cartItems[] = [
                    'slug'      => $dbProduct->slug,
                    'name'      => $dbProduct->name,
                    'unit'      => $dbProduct->unit ?? '',
                    'price'     => $dbProduct->price,
                    'old_price' => $dbProduct->old_price ?: null,
                    'qty'       => $item['qty'] ?? 1,
                    'image'     => $dbProduct->image_url,
                ];
            }
        }

        $count = array_sum(array_column($cartItems, 'qty'));
        $total = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $cartItems));
        session(['cart_items' => $cartItems, 'cart_count' => $count, 'cart_total' => $total]);
        session()->save();

        $formatted = number_format($total, 2);
        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: '✓ Items added back to your cart' } }));
            document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = '{$count}');
            document.querySelectorAll('[data-cart-total]').forEach(el => el.textContent = 'GH₳ {$formatted}');
            var mb = document.getElementById('mobile-cart-badge');
            if (mb) { mb.textContent = '{$count}'; mb.style.display = ''; }
        JS);
    }

    private function getFilteredOrders(): array
    {
        $all = $this->allOrders();
        if ($this->tab === 'all') {
            return $all;
        }
        return array_values(array_filter($all, fn ($o) => strtolower($o['status']) === $this->tab));
    }

    private function allOrders(): array
    {
        if (! auth()->check()) return [];

        $userId = auth()->id();
        $email  = auth()->user()->email;

        return Order::where(function ($q) use ($userId, $email) {
                // Prefer user_id match (set on new orders); fall back to email for legacy guest orders
                $q->where('user_id', $userId)
                  ->orWhere(function ($q2) use ($userId, $email) {
                      $q2->whereNull('user_id')->where('customer_email', $email);
                  });
            })
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id'     => $o->order_number,
                'date'   => $o->created_at->format('d M Y'),
                'items'  => is_array($o->items) ? $o->items : json_decode($o->items, true) ?? [],
                'total'  => $o->total,
                'status' => ucfirst($o->status),
            ])
            ->all();
    }

    public function render()
    {
        $orders = $this->getFilteredOrders();

        $tabs = [
            'all'        => 'All',
            'processing' => 'Processing',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
        ];

        return view('livewire.account-orders', compact('orders', 'tabs'));
    }
}
