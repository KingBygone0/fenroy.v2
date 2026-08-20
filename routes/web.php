<?php

use App\Http\Controllers\PaystackController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Auth\GoogleAuthController;

// ── POS Terminal (staff + admin only) ───────────────────────────
Route::get('/pos/login', \App\Livewire\Pos\Login::class)->name('pos.login')->middleware('guest');
Route::post('/pos/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('pos.login');
})->name('pos.logout')->middleware('auth');

Route::middleware(['auth', \App\Http\Middleware\RequirePosAccess::class, \App\Http\Middleware\RequireTwoFactor::class])->group(function () {
    Route::get('/pos',             \App\Livewire\Pos\Terminal::class)->name('pos.terminal');
    Route::get('/pos/products',   \App\Livewire\Pos\Products::class)->name('pos.products');
    Route::get('/pos/orders',     \App\Livewire\Pos\Orders::class)->name('pos.orders');
    Route::get('/pos/customers',  \App\Livewire\Pos\Customers::class)->name('pos.customers');
    Route::get('/pos/reports',    \App\Livewire\Pos\Reports::class)->name('pos.reports');
    Route::get('/pos/settings',   \App\Livewire\Pos\ProfileSettings::class)->name('pos.settings');
});

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

    // Google OAuth
    Route::get('/auth/google',          [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('home');
})->name('logout')->middleware('auth');


// ── Email verification ────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', \App\Livewire\Auth\VerifyEmail::class)
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('account.profile')->with('status', 'email-verified');
    })->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:3,1')->name('verification.send');
});

// ── Account ──────────────────────────────────────────────────────
Route::prefix('account')->name('account.')->middleware(['auth', 'verified'])->group(function () {
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
// Only show order confirmation to the session owner (order placed in this session).
// Unauthenticated visitors see a generic thank-you; the full detail is gated.
Route::get('/order-confirmed/{orderNumber?}', function ($orderNumber = null) {
    $order = null;
    if ($orderNumber) {
        $order = Order::where('order_number', $orderNumber)->first();
        // IDOR guard: only the owner (email match) may see order details.
        if ($order) {
            $authedEmail = auth()->user()?->email;
            if ($authedEmail !== $order->customer_email) {
                $order = null;  // hide details — show generic confirmation instead
            }
        }
    }
    return view('storefront.order-confirmed', compact('order', 'orderNumber'));
})->name('order.confirmed');

Route::get('/order/track/{orderNumber}', function ($orderNumber) {
    $order = Order::where('order_number', $orderNumber)
        ->where('customer_email', auth()->user()->email)  // ownership check — prevents IDOR
        ->first();
    if (! $order) {
        abort(404);  // same response whether order doesn't exist or belongs to another user
    }
    return view('storefront.order-tracking', compact('order', 'orderNumber'));
})->name('order.track')->middleware('auth');

// ── Receipt ──────────────────────────────────────────────────────
// Order numbers are FEN- + 12 random alphanumeric chars — cryptographically unguessable.
// No auth required so guests can access their receipt via the email link.
Route::get('/orders/receipt/{orderNumber}', function (string $orderNumber) {
    if (! preg_match('/^FEN-[A-Z0-9\-]{1,30}$/', $orderNumber)) {
        abort(404);
    }
    $order = Order::where('order_number', $orderNumber)->firstOrFail();
    return view('receipt', compact('order'));
})->name('order.receipt');

// ── Paystack ─────────────────────────────────────────────────────
Route::post('/paystack/verify',  [PaystackController::class, 'verify'])->name('paystack.verify')->middleware('throttle:20,1');
Route::post('/paystack/webhook', [PaystackController::class, 'webhook'])->name('paystack.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Admin: Product bulk import upload ────────────────────────────
Route::post('/admin/import-products/upload', function (Request $request) {
    abort_unless(auth()->user()?->is_admin, 403);

    $request->validate([
        'file' => 'required|string|max:4000000', // ~3MB base64 → ~2.25MB binary
        'name' => 'required|string|max:255',
        'ext'  => 'required|in:csv,xlsx',
    ]);

    // Strict base64 decode — returns false on invalid input
    $content = base64_decode($request->file, true);
    if ($content === false || strlen($content) === 0) {
        return response()->json(['error' => 'Invalid file data.'], 422);
    }

    // Validate actual file bytes match the declared extension
    $ext = $request->ext;
    if ($ext === 'xlsx') {
        // XLSX files start with PK\x03\x04 (ZIP magic bytes)
        if (! str_starts_with($content, "PK\x03\x04")) {
            return response()->json(['error' => 'File content does not match declared type.'], 422);
        }
    } else {
        // CSV: must be valid UTF-8 text, not a binary file
        if (! mb_check_encoding($content, 'UTF-8')) {
            return response()->json(['error' => 'CSV file must be UTF-8 encoded text.'], 422);
        }
        // Reject any content that looks like a PHP script
        if (preg_match('/<\?(?:php|=)/i', $content)) {
            return response()->json(['error' => 'Invalid file content.'], 422);
        }
    }

    // Cryptographically random filename — stored in private (non-web-accessible) directory
    $filename = Str::random(32) . '.' . $ext;
    $dir      = storage_path('app/imports');

    if (! is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $content);

    return response()->json(['token' => $filename]);
})->middleware(['auth'])->name('admin.import.upload');

// ── Search autocomplete ──────────────────────────────────────────
Route::get('/api/search-suggest', function (Request $request) {
    $q = substr(trim($request->query('q', '')), 0, 100);
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
    ]), 200, [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT);
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
        $xml .= '<url><loc>https://fenroy.shop/categories/' . htmlspecialchars($slug, ENT_XML1, 'UTF-8') . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>';
    }
    foreach ($products as $product) {
        $xml .= '<url><loc>https://fenroy.shop/products/' . htmlspecialchars($product->slug, ENT_XML1, 'UTF-8') . '</loc><lastmod>' . $product->updated_at->toAtomString() . '</lastmod><changefreq>weekly</changefreq><priority>0.6</priority></url>';
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');
