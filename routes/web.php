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
Route::post('/cart/quick-add', [CartController::class, 'quickAdd'])->name('cart.quick-add')->middleware('throttle:60,1');

// ── Checkout ─────────────────────────────────────────────────────
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

// ── Auth ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    \App\Livewire\Auth\Login::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register')->middleware('throttle:10,1');
    Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)->name('password.reset');
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
})->name('order.track')->middleware('auth');

// ── Paystack ─────────────────────────────────────────────────────
Route::post('/paystack/verify',  [PaystackController::class, 'verify'])->name('paystack.verify')->middleware('throttle:20,1');
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

// ── Search autocomplete ──────────────────────────────────────────
Route::get('/api/search-suggest', function (Request $request) {
    $q = trim($request->query('q', ''));
    if (strlen($q) < 2) return response()->json([]);
    $products = \App\Models\Product::where('is_active', true)
        ->where('name', 'like', '%' . $q . '%')
        ->orderByDesc('is_featured')
        ->limit(6)
        ->get(['name', 'slug', 'price', 'image']);
    return response()->json($products->map(fn ($p) => [
        'name'  => $p->name,
        'slug'  => $p->slug,
        'price' => (float) $p->price,
        'image' => $p->image_url,
    ]));
})->name('search.suggest')->middleware('throttle:30,1');

// ── Sitemap ──────────────────────────────────────────────────────
Route::get('/sitemap.xml', function () {
    $products   = \App\Models\Product::where('is_active', true)->get(['slug', 'updated_at']);
    $categories = ['fruits-vegetables','beverages','dairy-eggs','pantry','snacks-sweets','personal-care','household','baby-care'];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $staticPages = ['/', '/categories', '/search', '/deals', '/best-sellers', '/new-arrivals'];
    foreach ($staticPages as $page) {
        $xml .= '<url><loc>https://fenroy.shop' . $page . '</loc><changefreq>weekly</changefreq><priority>' . ($page === '/' ? '1.0' : '0.8') . '</priority></url>';
    }
    foreach ($categories as $slug) {
        $xml .= '<url><loc>https://fenroy.shop/categories/' . $slug . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>';
    }
    foreach ($products as $product) {
        $xml .= '<url><loc>https://fenroy.shop/products/' . $product->slug . '</loc><lastmod>' . $product->updated_at->toAtomString() . '</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>';
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');
