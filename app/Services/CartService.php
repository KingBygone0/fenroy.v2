<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;

class CartService
{
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public function addItem(Cart $cart, Product $product, int $qty): CartItem
    {
        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $newQty = min(99, $existing->quantity + $qty);
            $existing->update(['quantity' => $newQty]);
            return $existing->fresh();
        }

        return CartItem::create([
            'cart_id'    => $cart->id,
            'product_id' => $product->id,
            'slug'       => $product->slug,
            'quantity'   => min(99, $qty),
            'price'      => $product->price,
        ]);
    }

    public function updateItem(Cart $cart, int $cartItemId, int $qty): CartItem
    {
        $item = CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->firstOrFail();

        if ($qty <= 0) {
            $item->delete();
            return $item;
        }

        $item->update(['quantity' => min(99, $qty)]);
        return $item->fresh();
    }

    public function removeItem(Cart $cart, int $cartItemId): void
    {
        CartItem::where('id', $cartItemId)
            ->where('cart_id', $cart->id)
            ->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['coupon_code' => null, 'discount' => 0]);
    }

    public function applyCoupon(Cart $cart, string $code): array
    {
        $code   = strtoupper(trim($code));
        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        $subtotal = $cart->items->sum(fn ($i) => $i->price * $i->quantity);

        if (! $coupon || ! $coupon->isValid($subtotal)) {
            return ['success' => false, 'message' => 'This code is not valid or could not be applied.'];
        }

        $discount = $coupon->discountFor($subtotal);
        $cart->update(['coupon_code' => $code, 'discount' => $discount]);

        return ['success' => true, 'discount' => $discount, 'code' => $code];
    }

    public function toArray(Cart $cart): array
    {
        $cart->load('items.product');
        $items = [];
        foreach ($cart->items as $item) {
            $product = $item->product;
            if (! $product) continue;
            $items[] = [
                'id'        => $item->id,
                'slug'      => $item->slug,
                'name'      => $product->name,
                'unit'      => $product->unit,
                'price'     => $product->price,
                'old_price' => $product->old_price,
                'qty'       => $item->quantity,
                'image'     => $product->getImageUrlAttribute(),
            ];
        }
        return $items;
    }
}
