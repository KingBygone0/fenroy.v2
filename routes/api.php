<?php

use Illuminate\Support\Facades\Route;

// API routes are defined here. All routes in this file are automatically
// prefixed with /api and use the 'api' middleware group.

// Auth
Route::prefix('auth')->group(function () {
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('google', [\App\Http\Controllers\Api\AuthController::class, 'googleTokenLogin'])->middleware('throttle:10,1');
    Route::post('2fa/verify', [\App\Http\Controllers\Api\AuthController::class, 'twoFactorVerify'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::post('2fa/setup', [\App\Http\Controllers\Api\AuthController::class, 'twoFactorSetup']);
        Route::post('2fa/confirm', [\App\Http\Controllers\Api\AuthController::class, 'twoFactorConfirm']);
        Route::get('profile', [\App\Http\Controllers\Api\AuthController::class, 'profile']);
        Route::patch('profile', [\App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
        Route::post('password', [\App\Http\Controllers\Api\AuthController::class, 'changePassword']);
    });
});

// Public catalogue
Route::get('products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
Route::get('products/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
Route::get('categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
Route::get('delivery-zones', [\App\Http\Controllers\Api\DeliveryZoneController::class, 'index']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Cart
    Route::get('cart', [\App\Http\Controllers\Api\CartController::class, 'show']);
    Route::post('cart/items', [\App\Http\Controllers\Api\CartController::class, 'addItem']);
    Route::patch('cart/items/{cartItem}', [\App\Http\Controllers\Api\CartController::class, 'updateItem']);
    Route::delete('cart/items/{cartItem}', [\App\Http\Controllers\Api\CartController::class, 'removeItem']);
    Route::delete('cart', [\App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::post('cart/coupon', [\App\Http\Controllers\Api\CartController::class, 'applyCoupon'])->middleware('throttle:10,1');
    Route::delete('cart/coupon', [\App\Http\Controllers\Api\CartController::class, 'removeCoupon']);

    // Orders
    Route::get('orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::get('orders/{orderNumber}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::post('orders/checkout', [\App\Http\Controllers\Api\OrderController::class, 'checkout'])->middleware('throttle:5,1');
    Route::post('orders/paystack-callback', [\App\Http\Controllers\Api\OrderController::class, 'paystackCallback'])->middleware('throttle:20,1');

    // Addresses
    Route::get('addresses', [\App\Http\Controllers\Api\AddressController::class, 'index']);
    Route::post('addresses', [\App\Http\Controllers\Api\AddressController::class, 'store']);
    Route::patch('addresses/{address}', [\App\Http\Controllers\Api\AddressController::class, 'update']);
    Route::delete('addresses/{address}', [\App\Http\Controllers\Api\AddressController::class, 'destroy']);
    Route::post('addresses/{address}/default', [\App\Http\Controllers\Api\AddressController::class, 'setDefault']);

    // Wishlist
    Route::get('wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
    Route::post('wishlist/toggle', [\App\Http\Controllers\Api\WishlistController::class, 'toggle']);
});

// Coupon validation (rate limited, no auth required to check validity)
Route::get('coupons/validate', [\App\Http\Controllers\Api\CouponController::class, 'validateCoupon'])->middleware('throttle:10,1');
