<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        return view('storefront.cart');
    }

    public function quickAdd(Request $request): JsonResponse
    {
        $slug = trim((string) $request->input('slug', ''));

        if (! $slug) {
            return response()->json(['status' => 'error', 'message' => 'Invalid product.'], 422);
        }

        $product = Product::where('slug', $slug)->where('is_active', true)->first();

        if (! $product || $product->price <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Product not found.'], 404);
        }

        $cartItems = session('cart_items', []);
        $found = false;
        foreach ($cartItems as &$item) {
            if ($item['slug'] === $slug) {
                $item['qty']++;
                $item['price'] = $product->price;
                $found = true;
                break;
            }
        }
        unset($item);

        if (! $found) {
            $cartItems[] = [
                'slug'      => $product->slug,
                'name'      => $product->name,
                'unit'      => $product->unit ?? '',
                'price'     => $product->price,
                'old_price' => $product->old_price ?: null,
                'qty'       => 1,
                'image'     => $product->image_url,
            ];
        }

        $count = array_sum(array_column($cartItems, 'qty'));
        $total = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $cartItems));

        session(['cart_items' => $cartItems, 'cart_count' => $count, 'cart_total' => $total]);
        session()->save();

        return response()->json([
            'status'  => 'ok',
            'message' => '✓ ' . $product->name . ' added to cart',
            'count'   => $count,
            'total'   => number_format($total, 2),
        ]);
    }
}
