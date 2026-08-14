<?php

use App\Http\Controllers\PaystackController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\AccountController;

// ── Homepage ────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Search ───────────────────────────────────────────────────────
Route::get('/search', [SearchController::class, 'index'])->name('search');

// ── Special pages ────────────────────────────────────────────────
Route::get('/deals',        fn () => view('storefront.deals'))->name('deals');
Route::get('/new-arrivals', fn () => view('storefront.new-arrivals'))->name('new-arrivals');
Route::get('/best-sellers', fn () => view('storefront.best-sellers'))->name('best-sellers');

// ── Categories ───────────────────────────────────────────────────
Route::get('/categories',           [CategoryController::class, 'index'])->name('category.index');
Route::get('/categories/{slug}',    [CategoryController::class, 'show'])->name('category.show');

// ── Products ─────────────────────────────────────────────────────
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('product.show');

// ── Cart ─────────────────────────────────────────────────────────
Route::get('/cart',            [CartController::class, 'index'])->name('cart');
Route::post('/cart/quick-add', [CartController::class, 'quickAdd'])->name('cart.quick-add');

// ── Checkout ─────────────────────────────────────────────────────
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

// ── Auth ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    \App\Livewire\Auth\Login::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('home');
})->name('logout')->middleware('auth');

// ── Account ──────────────────────────────────────────────────────
Route::prefix('account')->name('account.')->middleware('auth')->group(function () {
    Route::get('/profile',   [AccountController::class, 'profile'])->name('profile');
    Route::get('/orders',    [AccountController::class, 'orders'])->name('orders');
    Route::get('/wishlist',  [AccountController::class, 'wishlist'])->name('wishlist');
    Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
});

// ── Info pages ───────────────────────────────────────────────────
Route::get('/about',    fn () => view('storefront.about'))->name('about');
Route::get('/delivery', function () {
    $zones = \App\Models\DeliveryZone::where('is_active', true)->orderBy('sort_order')->get();
    return view('storefront.delivery', compact('zones'));
})->name('delivery');
Route::get('/faq',      fn () => view('storefront.faq'))->name('faq');
Route::get('/contact',  fn () => view('storefront.contact'))->name('contact');
Route::get('/privacy',  fn () => view('storefront.privacy'))->name('privacy');

// ── Orders ───────────────────────────────────────────────────────
Route::get('/order-confirmed/{orderNumber?}', function ($orderNumber = null) {
    $order = $orderNumber ? Order::where('order_number', $orderNumber)->first() : null;
    return view('storefront.order-confirmed', compact('order', 'orderNumber'));
})->name('order.confirmed');

Route::get('/order/track/{orderNumber}', function ($orderNumber) {
    $order = Order::where('order_number', $orderNumber)->first();
    return view('storefront.order-tracking', compact('order', 'orderNumber'));
})->name('order.track');

// ── Paystack ─────────────────────────────────────────────────────
Route::post('/paystack/verify',  [PaystackController::class, 'verify'])->name('paystack.verify');
Route::post('/paystack/webhook', [PaystackController::class, 'webhook'])->name('paystack.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Admin: Product bulk import upload ────────────────────────────
Route::post('/admin/import-products/upload', function (Request $request) {
    abort_unless(auth()->user()?->is_admin, 403);

    $request->validate([
        'file' => 'required|string',
        'name' => 'required|string',
        'ext'  => 'required|in:csv,xlsx',
    ]);

    $content  = base64_decode($request->file);
    $filename = 'import-' . now()->format('YmdHis') . '-' . uniqid() . '.' . $request->ext;
    $dir      = storage_path('app/imports');

    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $content);

    return response()->json(['token' => $filename]);
})->middleware(['auth'])->name('admin.import.upload');
