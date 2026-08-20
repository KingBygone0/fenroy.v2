<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true);

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('is_featured') && $request->boolean('is_featured')) {
            $query->where('is_featured', true);
        }

        if ($request->has('is_best_seller') && $request->boolean('is_best_seller')) {
            $query->where('is_best_seller', true);
        }

        if ($request->has('sort')) {
            $sort = $request->input('sort');
            match ($sort) {
                'price_asc' => $query->orderBy('price', 'ASC'),
                'price_desc' => $query->orderBy('price', 'DESC'),
                'newest' => $query->orderBy('created_at', 'DESC'),
                default => null,
            };
        }

        $products = $query->cursorPaginate(20);

        return response()->json(new ProductCollection($products));
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $product->load(['reviews' => fn($q) => $q->where('is_approved', true)->latest()->take(20)]);

        $inWishlist = false;
        if (\auth('sanctum')->check()) {
            $inWishlist = Wishlist::where('user_id', \auth('sanctum')->id())
                ->where('product_id', $product->id)
                ->exists();
        }

        return response()->json(
            (new ProductResource($product))->additional(['in_wishlist' => $inWishlist])
        );
    }
}
