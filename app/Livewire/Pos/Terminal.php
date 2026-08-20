<?php

namespace App\Livewire\Pos;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Terminal extends Component
{
    public string $search          = '';
    public string $activeCategory  = '';
    public int    $page            = 1;
    public int    $perPage         = 18;

    public array  $cart            = [];
    public string $customerName    = 'Walk-in Customer';
    public string $customerPhone   = '';
    public string $customerEmail   = '';
    public string $paymentMethod   = 'cash';
    public float  $amountTendered  = 0;
    public float  $manualDiscount  = 0;
    public string $note            = '';

    public function updatedSearch(): void    { $this->page = 1; }
    public function updatedActiveCategory(): void { $this->page = 1; }

    #[Computed]
    public function categories(): array
    {
        return Product::where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    #[Computed]
    public function productsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Product::where('is_active', true)
            ->when($this->search !== '', fn ($q) =>
                $q->where(function ($q) {
                    $term = '%' . $this->search . '%';
                    $q->where('name', 'like', $term)
                      ->orWhere('sku', 'like', $term)
                      ->orWhere('category', 'like', $term);
                })
            )
            ->when($this->activeCategory !== '', fn ($q) =>
                $q->where('category', $this->activeCategory)
            )
            ->orderBy('name');
    }

    #[Computed]
    public function products(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->productsQuery
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();
    }

    #[Computed]
    public function totalProducts(): int
    {
        return $this->productsQuery->count();
    }

    #[Computed]
    public function totalPages(): int
    {
        return max(1, (int) ceil($this->totalProducts / $this->perPage));
    }

    public function setCategory(string $cat): void
    {
        $this->activeCategory = $this->activeCategory === $cat ? '' : $cat;
    }

    public function prevPage(): void { $this->page = max(1, $this->page - 1); }
    public function nextPage(): void { $this->page = min($this->totalPages, $this->page + 1); }
    public function goToPage(int $p): void { $this->page = max(1, min($this->totalPages, $p)); }

    public function addToCart(string $slug): void
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->first();
        if (! $product) return;

        foreach ($this->cart as &$item) {
            if ($item['slug'] === $slug) {
                $item['qty'] = min($item['qty'] + 1, $product->stock ?? 999);
                return;
            }
        }

        $this->cart[] = [
            'slug'  => $product->slug,
            'name'  => $product->name,
            'unit'  => $product->unit ?? '',
            'price' => $product->price,
            'stock' => $product->stock,
            'image' => $product->image_url,
            'qty'   => 1,
        ];
    }

    public function removeFromCart(string $slug): void
    {
        $this->cart = array_values(array_filter($this->cart, fn ($i) => $i['slug'] !== $slug));
    }

    public function updateQty(string $slug, int $qty): void
    {
        if ($qty <= 0) { $this->removeFromCart($slug); return; }
        foreach ($this->cart as &$item) {
            if ($item['slug'] === $slug) {
                $item['qty'] = min($qty, $item['stock'] ?? 999);
                return;
            }
        }
    }

    public function clearCart(): void
    {
        $this->cart           = [];
        $this->customerName   = 'Walk-in Customer';
        $this->customerPhone  = '';
        $this->customerEmail  = '';
        $this->paymentMethod  = 'cash';
        $this->amountTendered = 0;
        $this->manualDiscount = 0;
        $this->note           = '';
    }

    public function completeSale(): void
    {
        if (empty($this->cart)) return;

        if (! in_array($this->paymentMethod, ['cash', 'momo', 'card'], true)) {
            $this->dispatch('toast', message: 'Invalid payment method selected.');
            return;
        }

        $subtotal = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $this->cart));
        $discount = max(0, min((float) $this->manualDiscount, $subtotal));
        $total    = max(0, $subtotal - $discount);

        $customerEmail = trim($this->customerEmail);
        $customerEmail = filter_var($customerEmail, FILTER_VALIDATE_EMAIL)
            ? mb_substr($customerEmail, 0, 254)
            : null;

        $order = Order::create([
            'order_number'     => 'FEN-' . strtoupper(Str::random(12)),
            'is_walk_in'       => true,
            'customer_name'    => mb_substr(strip_tags(trim($this->customerName) ?: 'Walk-in Customer'), 0, 100),
            'customer_email'   => $customerEmail,
            'customer_phone'   => mb_substr(strip_tags(trim($this->customerPhone)), 0, 20) ?: null,
            'delivery_address' => 'Walk-in Sale',
            'delivery_window'  => null,
            'total'            => $total,
            'delivery_fee'     => 0,
            'discount'         => $discount,
            'coupon_code'      => null,
            'items'            => $this->cart,
            'status'           => 'delivered',
            'payment_status'   => 'paid',
            'payment_method'   => $this->paymentMethod,
            'paystack_ref'     => null,
            'notes'            => mb_substr(strip_tags(trim($this->note)), 0, 500) ?: null,
        ]);

        foreach ($this->cart as $item) {
            $qty = (int) $item['qty'];
            // GREATEST(0, stock - qty) prevents stock from going negative on concurrent sales
            Product::where('slug', $item['slug'])
                ->whereNotNull('stock')
                ->update(['stock' => DB::raw('GREATEST(0, stock - ' . $qty . ')')]);
        }

        $orderNumber = $order->order_number;
        $this->clearCart();
        $this->dispatch('sale-complete', orderNumber: $orderNumber);
    }

    public function render()
    {
        return view('livewire.pos.terminal')
            ->layout('layouts.pos');
    }
}
