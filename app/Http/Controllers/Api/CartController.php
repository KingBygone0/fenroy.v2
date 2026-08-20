<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart->load('items.product');
        return response()->json(new CartResource($cart));
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active) {
            return response()->json(['message' => 'Product is not available.'], 422);
        }

        if ($product->stock !== null && $product->stock <= 0) {
            return response()->json(['message' => 'Product is out of stock.'], 422);
        }

        $cart = $this->cartService->getOrCreateCart($request->user());
        $this->cartService->addItem($cart, $product, $request->quantity);
        $cart->load('items.product');

        return response()->json(new CartResource($cart), 201);
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $cart = $cartItem->cart;
        $this->cartService->updateItem($cart, $cartItem->id, $request->quantity);
        $cart->load('items.product');

        return response()->json(new CartResource($cart));
    }

    public function removeItem(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $cart = $cartItem->cart;
        $this->cartService->removeItem($cart, $cartItem->id);
        $cart->load('items.product');

        return response()->json(new CartResource($cart));
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $this->cartService->clearCart($cart);

        return response()->json(['message' => 'Cart cleared.']);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:50']);

        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart->load('items');

        $result = $this->cartService->applyCoupon($cart, $request->code);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => 'Coupon applied.', 'discount' => $result['discount']]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart->update(['coupon_code' => null, 'discount' => 0]);

        return response()->json(['message' => 'Coupon removed.']);
    }
}
