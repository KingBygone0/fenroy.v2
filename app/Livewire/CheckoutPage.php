<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;

class CheckoutPage extends Component
{
    #[Rule('required|min:2', message: 'Enter your full name.')]
    public string $name = '';

    #[Rule('required|regex:/^[0-9+\s]{9,15}$/', message: 'Enter a valid phone number.')]
    public string $phone = '';

    #[Rule('required|email', message: 'Enter a valid email address.')]
    public string $email = '';

    #[Rule('required|min:5', message: 'Enter your delivery address.')]
    public string $address = '';

    public int    $zoneId         = 0;

    #[Rule('required|in:morning,afternoon,evening')]
    public string $deliveryWindow = 'morning';

    #[Rule('required|in:call,allow,none')]
    public string $substitution   = 'call';

    public string $paymentMethod  = 'card';
    public string $couponCode     = '';
    public string $couponError    = '';
    public bool   $placing        = false;

    public function mount(): void
    {
        if (auth()->check()) {
            $user        = auth()->user();
            $this->name  = $user->name;
            $this->email = $user->email;

            $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first()
                ?? Address::where('user_id', $user->id)->latest()->first();

            if ($defaultAddress) {
                $this->phone   = $defaultAddress->phone;
                $this->address = $defaultAddress->line1 . ', ' . $defaultAddress->city . ', ' . $defaultAddress->region;
            }
        }
    }

    public function applyCoupon(): void
    {
        $this->couponError = '';

        $key = 'coupon.checkout:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->couponError = 'Too many attempts. Please wait before trying again.';
            return;
        }
        RateLimiter::hit($key, 60);

        $code = strtoupper(trim($this->couponCode));

        if ($code === '') {
            $this->couponError = 'Enter a coupon code.';
            return;
        }

        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (! $coupon || ! $coupon->isValid($this->subtotal)) {
            // Generic message — prevents enumeration of valid codes and threshold disclosure.
            $this->couponError = 'This code is not valid or could not be applied.';
            return;
        }

        $discount = $coupon->discountFor($this->subtotal);
        session(['cart_discount' => $discount, 'cart_coupon' => $code]);
        session()->save();
        $this->couponCode  = '';
        $this->couponError = '';
    }

    public function removeCoupon(): void
    {
        session()->forget(['cart_discount', 'cart_coupon']);
    }

    public function placeOrder(): void
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('toast', message: 'Please fill in all required fields.');
            throw $e;
        }

        if (! $this->zoneId) {
            $this->addError('zoneId', 'Please select your delivery area.');
            return;
        }

        $zone = DeliveryZone::find($this->zoneId);
        if (! $zone) {
            $this->addError('zoneId', 'Invalid delivery area selected.');
            return;
        }

        $this->placing = true;

        // ── Step 1: Require a non-empty real cart ─────────────────────────────
        $rawItems = session('cart_items', []);
        if (empty($rawItems)) {
            $this->dispatch('toast', message: 'Your cart is empty.');
            $this->placing = false;
            return;
        }

        // ── Step 2: Re-validate every item against the database ───────────────
        // Never trust session prices from add-to-cart time. Re-fetch:
        //   - Current price (price changes since add-to-cart are honoured)
        //   - is_active (deactivated products are removed from the order)
        //   - stock (quantity is capped to available stock; zero-stock items are dropped)
        $slugs      = array_values(array_filter(array_column($rawItems, 'slug')));
        $dbProducts = ! empty($slugs)
            ? Product::whereIn('slug', $slugs)->where('is_active', true)->get()->keyBy('slug')
            : collect();

        $items = [];
        foreach ($rawItems as $item) {
            $slug    = $item['slug'] ?? null;
            $product = $slug ? $dbProducts->get($slug) : null;

            if (! $product) continue; // skip deactivated or slug-less items

            $qty = max(1, min(99, (int) ($item['qty'] ?? 1)));

            // Cap quantity to available stock when the product tracks it
            if ($product->stock !== null) {
                if ($product->stock <= 0) continue; // out of stock — drop item
                $qty = min($qty, $product->stock);
            }

            $items[] = array_merge($item, [
                'price' => $product->price, // always use current DB price
                'name'  => $product->name,
                'qty'   => $qty,
            ]);
        }

        if (empty($items)) {
            $this->dispatch('toast', message: 'Your cart contains no available products.');
            $this->placing = false;
            return;
        }

        $subtotal = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $items));

        // ── Step 2b: Enforce minimum order amount ─────────────────────────────
        $minOrder = (float) Setting::get('minimum_order_amount', '0');
        if ($minOrder > 0 && $subtotal < $minOrder) {
            $this->dispatch('toast', message: 'Minimum order amount is GH₵ ' . number_format($minOrder, 2) . '.');
            $this->placing = false;
            return;
        }

        // ── Step 3: Re-validate coupon server-side at checkout time ──────────
        // The discount stored in session was computed at applyCoupon() time and may
        // now be stale: items could have been removed (reducing subtotal below the
        // coupon minimum), the coupon could have expired, or max_uses reached.
        // Always recalculate from the CURRENT subtotal, not the session amount.
        $discount   = 0;
        $couponCode = session('cart_coupon');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->discountFor($subtotal); // recalculate from current subtotal
            } else {
                session()->forget(['cart_discount', 'cart_coupon']);
                $couponCode = null;
            }
        }

        $deliveryFee = ($zone->free_above && $subtotal >= $zone->free_above) ? 0.00 : (float) $zone->fee;
        $total       = max(0, $subtotal + $deliveryFee - $discount);

        session(['pending_order' => [
            'user_id'         => auth()->id(),
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'address'         => $this->address,
            'delivery_zone'   => $zone->name,
            'delivery_window' => $this->deliveryWindowLabel,
            'items'           => $items,
            'subtotal'        => $subtotal,
            'delivery_fee'    => $deliveryFee,
            'discount'        => $discount,
            'coupon_code'     => $couponCode,
            'total'           => $total,
        ]]);

        $ref = 'FEN-' . strtoupper(Str::random(20));
        $this->js(sprintf(
            "window.dispatchEvent(new CustomEvent('fenroy:paystack', { detail: %s }))",
            json_encode([
                'email'    => $this->email,
                'amount'   => (int) round($total * 100),
                'currency' => 'GHS',
                'ref'      => $ref,
                'name'     => $this->name,
                'phone'    => $this->phone,
            ])
        ));
    }

    #[Computed]
    public function orderItems(): array
    {
        return session('cart_items', []);
    }

    #[Computed]
    public function subtotal(): float
    {
        return array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $this->orderItems));
    }

    #[Computed]
    public function selectedZone(): ?DeliveryZone
    {
        return $this->zoneId ? DeliveryZone::find($this->zoneId) : null;
    }

    #[Computed]
    public function deliveryFee(): float
    {
        $freeAbove = (float) Setting::get('free_delivery_above', '0');
        if ($freeAbove > 0 && $this->subtotal >= $freeAbove) return 0.00;

        $zone = $this->selectedZone;
        if (! $zone) return (float) Setting::get('delivery_fee', '0');
        if ($zone->free_above && $this->subtotal >= $zone->free_above) return 0.00;
        return (float) $zone->fee;
    }

    #[Computed]
    public function discount(): float
    {
        return (float) session('cart_discount', 0);
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal + $this->deliveryFee - $this->discount);
    }

    #[Computed]
    public function appliedCoupon(): ?string
    {
        return session('cart_coupon');
    }

    #[Computed]
    public function deliveryWindowLabel(): string
    {
        return match ($this->deliveryWindow) {
            'morning'   => 'Today, 8am – 12pm',
            'afternoon' => 'Today, 12pm – 4pm',
            'evening'   => 'Today, 4pm – 8pm',
            default     => '',
        };
    }

    public function render()
    {
        return view('livewire.checkout-page', [
            'zones'               => DeliveryZone::where('is_active', true)->orderBy('sort_order')->get(),
            'orderItems'          => $this->orderItems,
            'subtotal'            => $this->subtotal,
            'selectedZone'        => $this->selectedZone,
            'deliveryFee'         => $this->deliveryFee,
            'discount'            => $this->discount,
            'total'               => $this->total,
            'deliveryWindowLabel' => $this->deliveryWindowLabel,
            'appliedCoupon'       => $this->appliedCoupon,
        ]);
    }


}
