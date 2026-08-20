<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(CategoryResource::collection($categories));
    }
}
