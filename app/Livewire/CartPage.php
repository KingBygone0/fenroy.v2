<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Product;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CartPage extends Component
{
    #[Locked]
    public array   $items        = [];
    public string  $couponInput  = '';
    public string  $couponError  = '';

    public function mount(): void
    {
        $this->items = session('cart_items', []);
    }

    // ── Qty ────────────────────────────────────────────────────────
    public function increment(int $index): void
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['qty'] = min(99, $this->items[$index]['qty'] + 1);
        }
        $this->syncSession();
    }

    public function decrement(int $index): void
    {
        if (! isset($this->items[$index])) return;

        if ($this->items[$index]['qty'] <= 1) {
            $this->removeItem($index);
            return;
        }
        $this->items[$index]['qty']--;
        $this->syncSession();
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->syncSession();
    }

    // ── Coupon ─────────────────────────────────────────────────────
    public function applyCoupon(): void
    {
        $this->couponError = '';

        $key = 'coupon.cart:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->couponError = 'Too many attempts. Please wait before trying again.';
            return;
        }
        RateLimiter::hit($key, 60);

        $code = strtoupper(trim($this->couponInput));

        if ($code === '') {
            $this->couponError = 'Enter a coupon code.';
            return;
        }

        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (! $coupon || ! $coupon->isValid($this->subtotal)) {
            // Generic message — does not reveal whether the code exists, is expired,
            // or has a minimum threshold (prevents enumeration + oracle attacks).
            $this->couponError = 'This code is not valid or could not be applied.';
            return;
        }

        $discount = $coupon->discountFor($this->subtotal);
        session(['cart_discount' => $discount, 'cart_coupon' => $code]);
        session()->save();
        $this->couponInput = '';
    }

    public function removeCoupon(): void
    {
        session()->forget(['cart_discount', 'cart_coupon']);
        $this->couponError = '';
    }

    // ── Computed ───────────────────────────────────────────────────
    #[Computed]
    public function subtotal(): float
    {
        return array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $this->items));
    }

    #[Computed]
    public function appliedCoupon(): ?string
    {
        return session('cart_coupon');
    }

    #[Computed]
    public function discountAmount(): float
    {
        return (float) session('cart_discount', 0);
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal - $this->discountAmount);
    }

    #[Computed]
    public function minDeliveryFee(): float
    {
        return (float) (DeliveryZone::where('is_active', true)->min('fee') ?? 8);
    }

    #[Computed]
    public function minFreeThreshold(): float
    {
        return (float) (DeliveryZone::where('is_active', true)->whereNotNull('free_above')->min('free_above') ?? 180);
    }

    #[Computed]
    public function deliveryProgress(): float
    {
        return min(100, ($this->subtotal / $this->minFreeThreshold) * 100);
    }

    #[Computed]
    public function amountToFreeDelivery(): float
    {
        return max(0, $this->minFreeThreshold - $this->subtotal);
    }

    #[Computed]
    public function qualifiesForFreeDelivery(): bool
    {
        return $this->subtotal >= $this->minFreeThreshold;
    }

    public function render()
    {
        return view('livewire.cart-page', [
            'subtotal'                 => $this->subtotal,
            'appliedCoupon'            => $this->appliedCoupon,
            'discountAmount'           => $this->discountAmount,
            'total'                    => $this->total,
            'minDeliveryFee'           => $this->minDeliveryFee,
            'minFreeThreshold'         => $this->minFreeThreshold,
            'deliveryProgress'         => $this->deliveryProgress,
            'amountToFreeDelivery'     => $this->amountToFreeDelivery,
            'qualifiesForFreeDelivery' => $this->qualifiesForFreeDelivery,
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────
    private function syncSession(): void
    {
        $count = array_sum(array_column($this->items, 'qty'));
        $total = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $this->items));
        session(['cart_items' => $this->items, 'cart_count' => $count, 'cart_total' => $total]);
        session()->save();

        $formatted = number_format($total, 2);
        $this->js(<<<JS
            document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = '{$count}');
            document.querySelectorAll('[data-cart-total]').forEach(el => el.textContent = 'GH₵ {$formatted}');
            var mb = document.getElementById('mobile-cart-badge');
            if (mb) { mb.textContent = '{$count}'; mb.style.display = {$count} > 0 ? '' : 'none'; }
        JS);
    }
}
