<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Setting;

class CheckoutService
{
    public function validateAndPriceItems(array $rawItems): array
    {
        $slugs      = array_values(array_filter(array_column($rawItems, 'slug')));
        $dbProducts = ! empty($slugs)
            ? Product::whereIn('slug', $slugs)->where('is_active', true)->get()->keyBy('slug')
            : collect();

        $items = [];
        foreach ($rawItems as $item) {
            $slug    = $item['slug'] ?? null;
            $product = $slug ? $dbProducts->get($slug) : null;

            if (! $product) continue;

            $qty = max(1, min(99, (int) ($item['qty'] ?? 1)));

            if ($product->stock !== null) {
                if ($product->stock <= 0) continue;
                $qty = min($qty, $product->stock);
            }

            $items[] = array_merge($item, [
                'price' => $product->price,
                'name'  => $product->name,
                'qty'   => $qty,
            ]);
        }

        return $items;
    }

    public function computeTotals(array $items, int $zoneId, ?string $couponCode): array
    {
        $subtotal = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $items));

        $minOrder = (float) Setting::get('minimum_order_amount', '0');
        if ($minOrder > 0 && $subtotal < $minOrder) {
            throw new \InvalidArgumentException('Minimum order amount is GH₵ ' . number_format($minOrder, 2) . '.');
        }

        $discount = 0;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->discountFor($subtotal);
            } else {
                $couponCode = null;
            }
        }

        $zone        = DeliveryZone::findOrFail($zoneId);
        $deliveryFee = ($zone->free_above && $subtotal >= $zone->free_above) ? 0.00 : (float) $zone->fee;
        $total       = max(0, $subtotal + $deliveryFee - $discount);

        return [
            'subtotal'     => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount'     => $discount,
            'coupon_code'  => $couponCode,
            'total'        => $total,
            'zone'         => $zone,
        ];
    }
}
