<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (empty(session('cart_items', []))) {
            return redirect()->route('cart');
        }

        return view('storefront.checkout');
    }
}
