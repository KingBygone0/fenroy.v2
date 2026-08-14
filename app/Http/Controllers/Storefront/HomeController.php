<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = [
            ['name' => 'Fruits & Veg',   'slug' => 'fruits-vegetables', 'image' => 'cat-fruits-veg.webp'],
            ['name' => 'Beverages',       'slug' => 'beverages',          'image' => 'cat-beverages.jpg'],
            ['name' => 'Snacks & Sweets', 'slug' => 'snacks-sweets',      'image' => 'cat-snacks.png'],
            ['name' => 'Pantry',          'slug' => 'pantry',             'image' => 'cat-pantry.png'],
            ['name' => 'Dairy & Eggs',    'slug' => 'dairy-eggs',         'image' => 'cat-dairy-eggs.jpg'],
            ['name' => 'Household',       'slug' => 'household',          'image' => 'cat-household.png'],
            ['name' => 'Personal Care',   'slug' => 'personal-care',      'image' => 'cat-personal-care.jpg'],
            ['name' => 'Baby Care',       'slug' => 'baby-care',          'image' => 'cat-baby-care.jpg'],
        ];

        $featuredProducts = Product::where('is_featured', true)->where('is_active', true)
            ->limit(8)->get()->map->toCardArray()->all();

        $bestSellers = Product::where('is_best_seller', true)->where('is_active', true)
            ->limit(8)->get()->map->toCardArray()->all();

        return view('storefront.home', compact('categories', 'featuredProducts', 'bestSellers'));
    }
}
