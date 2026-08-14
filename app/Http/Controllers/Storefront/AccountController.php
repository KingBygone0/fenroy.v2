<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function profile(): View
    {
        return view('storefront.account.profile');
    }

    public function orders(): View
    {
        return view('storefront.account.orders');
    }

    public function wishlist(): View
    {
        return view('storefront.account.wishlist');
    }

    public function addresses(): View
    {
        return view('storefront.account.addresses');
    }
}
