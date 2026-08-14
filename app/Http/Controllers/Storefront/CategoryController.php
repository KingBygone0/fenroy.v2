<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    private const CATEGORIES = [
        'fruits-vegetables' => ['name' => 'Fruits & Vegetables', 'image' => 'cat-fruits-veg.webp'],
        'beverages'         => ['name' => 'Beverages',           'image' => 'cat-beverages.jpg'],
        'snacks-sweets'     => ['name' => 'Snacks & Sweets',     'image' => 'cat-snacks.png'],
        'pantry'            => ['name' => 'Pantry',              'image' => 'cat-pantry.png'],
        'dairy-eggs'        => ['name' => 'Dairy & Eggs',        'image' => 'cat-dairy-eggs.jpg'],
        'household'         => ['name' => 'Household',           'image' => 'cat-household.png'],
        'personal-care'     => ['name' => 'Personal Care',       'image' => 'cat-personal-care.jpg'],
        'baby-care'         => ['name' => 'Baby Care',           'image' => 'cat-baby-care.jpg'],
    ];

    public function index(): View
    {
        $counts = Product::where('is_active', true)
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = collect(self::CATEGORIES)
            ->map(fn ($cat, $slug) => array_merge($cat, ['slug' => $slug, 'count' => $counts[$slug] ?? 0]))
            ->values()
            ->all();

        return view('storefront.category-index', compact('categories'));
    }

    public function show(string $slug): View
    {
        $category = self::CATEGORIES[$slug] ?? self::CATEGORIES['fruits-vegetables'];

        return view('storefront.category', [
            'slug'         => $slug,
            'categoryName' => $category['name'],
        ]);
    }
}
