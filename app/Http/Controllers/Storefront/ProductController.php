<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        return view('storefront.product', compact('slug'));
    }
}
