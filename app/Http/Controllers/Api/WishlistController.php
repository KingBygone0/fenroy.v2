<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Wishlist::where('user_id', $request->user()->id)->with('product')->get();

        return response()->json(WishlistResource::collection($items));
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);

        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['wishlisted' => false]);
        }

        Wishlist::create(['user_id' => $request->user()->id, 'product_id' => $request->product_id]);

        return response()->json(['wishlisted' => true]);
    }
}
