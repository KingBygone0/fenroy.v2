<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Wishlist;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AccountWishlist extends Component
{
    #[Locked]
    public array $items = [];

    public function mount(): void
    {
        $this->loadItems();
    }

    private function loadItems(): void
    {
        if (auth()->check()) {
            $this->items = Wishlist::where('user_id', auth()->id())
                ->with('product')
                ->get()
                ->map(fn ($w) => $w->product ? $w->product->toCardArray() + ['wishlist_id' => $w->id] : null)
                ->filter()
                ->values()
                ->all();
        } else {
            $this->items = [];
        }
    }

    public function moveToCart(int $index): void
    {
        $item = $this->items[$index] ?? null;
        if (! $item) return;

        $dbProduct = Product::where('slug', $item['slug'])->where('is_active', true)->first();
        if (! $dbProduct) return;

        $cartItems = session('cart_items', []);
        $found = false;
        foreach ($cartItems as &$ci) {
            if ($ci['slug'] === $item['slug']) {
                $ci['qty']++;
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
                'qty'       => 1,
                'image'     => $dbProduct->image_url,
            ];
        }

        $count = array_sum(array_column($cartItems, 'qty'));
        $total = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $cartItems));
        session(['cart_items' => $cartItems, 'cart_count' => $count, 'cart_total' => $total]);
        session()->save();

        $name      = json_encode('✓ ' . $item['name'] . ' added to cart');
        $formatted = number_format($total, 2);
        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: {$name} } }));
            document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = '{$count}');
            document.querySelectorAll('[data-cart-total]').forEach(el => el.textContent = 'GH₳ {$formatted}');
            var mb = document.getElementById('mobile-cart-badge');
            if (mb) { mb.textContent = '{$count}'; mb.style.display = ''; }
        JS);
    }

    public function removeItem(int $index): void
    {
        $item = $this->items[$index] ?? null;
        if (! $item) return;

        if (auth()->check() && isset($item['wishlist_id'])) {
            Wishlist::where('id', $item['wishlist_id'])->where('user_id', auth()->id())->delete();
        }

        array_splice($this->items, $index, 1);
    }

    public function render()
    {
        return view('livewire.account-wishlist');
    }
}
