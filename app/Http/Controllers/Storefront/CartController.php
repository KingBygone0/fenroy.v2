<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
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
        $slug     = (string)  $request->input('slug', '');
        $name     = (string)  $request->input('name', '');
        $unit     = (string)  $request->input('unit', '');
        $price    = (float)   $request->input('price', 0);
        $oldPrice = $request->input('old_price') ? (float) $request->input('old_price') : null;
        $image    = (string)  $request->input('image', '');

        if (! $slug || ! $name || $price <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Invalid product.'], 422);
        }

        $cartItems = session('cart_items', []);
        $found = false;
        foreach ($cartItems as &$item) {
            if ($item['slug'] === $slug) {
                $item['qty']++;
                $found = true;
                break;
            }
        }
        unset($item);

        if (! $found) {
            $cartItems[] = [
                'slug'      => $slug,
                'name'      => $name,
                'unit'      => $unit,
                'price'     => $price,
                'old_price' => $oldPrice,
                'qty'       => 1,
                'image'     => $image,
            ];
        }

        $count = array_sum(array_column($cartItems, 'qty'));
        $total = array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $cartItems));

        session(['cart_items' => $cartItems, 'cart_count' => $count, 'cart_total' => $total]);
        session()->save();

        return response()->json([
            'status'  => 'ok',
            'message' => '✓ ' . $name . ' added to cart',
            'count'   => $count,
            'total'   => number_format($total, 2),
        ]);
    }
}
