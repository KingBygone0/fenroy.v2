<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Product;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

class PointOfSale extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'Point of Sale';

    protected static \UnitEnum|string|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'pos';

    protected string $view = 'filament.pages.point-of-sale';

    public string $search         = '';
    public array  $cart           = [];
    public string $customerName   = 'Walk-in Customer';
    public string $customerPhone  = '';
    public string $customerEmail  = '';
    public string $paymentMethod  = 'cash';
    public float  $amountTendered = 0;
    public float  $manualDiscount = 0;
    public string $note           = '';
    public bool   $completing     = false;

    #[Computed]
    public function products(): \Illuminate\Database\Eloquent\Collection
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
            ->orderBy('name')
            ->limit(60)
            ->get();
    }

    public function addToCart(string $slug): void
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->first();
        if (! $product) return;

        foreach ($this->cart as &$item) {
            if ($item['slug'] === $slug) {
                $max = $product->stock ?? 999;
                $item['qty'] = min($item['qty'] + 1, $max);
                return;
            }
        }

        $this->cart[] = [
            'slug'  => $product->slug,
            'name'  => $product->name,
            'unit'  => $product->unit ?? '',
            'price' => $product->price,
            'stock' => $product->stock,
            'qty'   => 1,
        ];
    }

    public function removeFromCart(string $slug): void
    {
        $this->cart = array_values(array_filter($this->cart, fn ($i) => $i['slug'] !== $slug));
    }

    public function updateQty(string $slug, int $qty): void
    {
        if ($qty <= 0) {
            $this->removeFromCart($slug);
            return;
        }

        foreach ($this->cart as &$item) {
            if ($item['slug'] === $slug) {
                $max = $item['stock'] ?? 999;
                $item['qty'] = min($qty, $max);
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
        if (empty($this->cart)) {
            $this->dispatch('pos-error', message: 'Cart is empty.');
            return;
        }

        $this->completing = true;

        $subtotal = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $this->cart));
        $discount = max(0, min((float) $this->manualDiscount, $subtotal));
        $total    = max(0, $subtotal - $discount);

        $customerName = trim($this->customerName) ?: 'Walk-in Customer';

        $order = Order::create([
            'order_number'    => 'FEN-' . strtoupper(Str::random(12)),
            'is_walk_in'      => true,
            'customer_name'   => $customerName,
            'customer_email'  => trim($this->customerEmail) ?: null,
            'customer_phone'  => trim($this->customerPhone) ?: null,
            'delivery_address'=> 'Walk-in Sale',
            'delivery_window' => null,
            'total'           => $total,
            'delivery_fee'    => 0,
            'discount'        => $discount,
            'coupon_code'     => null,
            'items'           => $this->cart,
            'status'          => 'delivered',
            'payment_status'  => 'paid',
            'payment_method'  => $this->paymentMethod,
            'paystack_ref'    => null,
            'notes'           => trim($this->note) ?: null,
        ]);

        // Decrement stock
        foreach ($this->cart as $item) {
            Product::where('slug', $item['slug'])
                ->whereNotNull('stock')
                ->decrement('stock', $item['qty']);
        }

        $orderNumber = $order->order_number;
        $this->clearCart();
        $this->completing = false;

        $this->dispatch('pos-sale-complete', orderNumber: $orderNumber);
    }
}
