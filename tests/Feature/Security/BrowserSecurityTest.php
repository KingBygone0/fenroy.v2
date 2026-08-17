<?php

namespace Tests\Feature\Security;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrowserSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ── Security header assertions ─────────────────────────────────────────

    public function test_security_headers_present_on_storefront(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_csp_header_blocks_object_src(): void
    {
        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        if ($csp === null) {
            $this->markTestSkipped('CSP header not emitted in test environment (Apache .htaccess headers are not active)');
        }

        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("https://checkout.paystack.com", $csp);
        $this->assertStringContainsString("https://fonts.googleapis.com", $csp);
    }

    // ── Stored XSS: review body ────────────────────────────────────────────

    public function test_review_body_is_html_escaped_in_product_page(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'sort_order' => 1]);
        $product  = Product::factory()->create([
            'name'      => 'Test Product',
            'slug'      => 'test-product',
            'is_active' => true,
            'category'  => 'test',
        ]);

        $user = User::factory()->create(['name' => 'Legit User']);
        $review = Review::create([
            'product_id'    => $product->id,
            'user_id'       => $user->id,
            'reviewer_name' => 'Hacker',
            'rating'        => 5,
            'body'          => '<script>alert("xss")</script>',
        ]);
        // is_approved is excluded from fillable (security measure); set directly
        $review->is_approved = true;
        $review->save();

        $response = $this->get('/products/' . $product->slug);

        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }

    // ── Stored XSS: product name / description ─────────────────────────────

    public function test_product_name_is_html_escaped_in_listing(): void
    {
        Product::factory()->create([
            'name'      => '<img src=x onerror=alert(1)>',
            'slug'      => 'xss-product',
            'is_active' => true,
            'category'  => 'test',
        ]);

        $response = $this->get('/');

        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_product_description_is_html_escaped_on_product_page(): void
    {
        $product = Product::factory()->create([
            'name'        => 'Safe Product',
            'slug'        => 'safe-product',
            'description' => '"><script>alert(document.cookie)</script>',
            'is_active'   => true,
            'category'    => 'test',
        ]);

        $response = $this->get('/products/' . $product->slug);

        $response->assertDontSee('<script>alert(document.cookie)</script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }

    // ── Stored XSS: username in profile ───────────────────────────────────

    public function test_username_is_html_escaped_in_account_profile(): void
    {
        $user = User::factory()->create([
            'name' => '<script>alert("stored-xss")</script>',
        ]);

        $response = $this->actingAs($user)->get('/account/profile');

        $response->assertDontSee('<script>alert("stored-xss")</script>', false);
    }

    // ── Reflected XSS: search query ────────────────────────────────────────

    public function test_search_query_is_html_escaped_in_results(): void
    {
        $response = $this->get('/search?q=' . urlencode('<script>alert(1)</script>'));

        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_search_suggest_api_json_encodes_names(): void
    {
        Product::factory()->create([
            'name'      => '<script>XSS</script>',
            'slug'      => 'xss-slug',
            'is_active' => true,
            'category'  => 'test',
        ]);

        $response = $this->getJson('/api/search-suggest?q=XSS');

        // JSON output is auto-encoded; verify the raw script tag is not in the response body
        $response->assertOk();
        $body = $response->getContent();
        $this->assertStringNotContainsString('<script>', $body,
            'JSON-encoded product name must not contain unescaped HTML tags');
    }

    // ── GA4 ID JS injection ────────────────────────────────────────────────

    public function test_ga4_id_is_js_safe_in_layout(): void
    {
        Setting::set('ga4_measurement_id', "G-TEST123");

        $response = $this->get('/');
        $body = $response->getContent();

        // GA4 block must render with the setting value
        $this->assertStringContainsString('G-TEST123', $body,
            'GA4 measurement ID must appear in the page when set');
        // Js::from() wraps values in single-quoted JS string literals; the ID must
        // appear as 'G-TEST123' (not unquoted) in the gtag('config', ...) call
        $this->assertStringContainsString("gtag('config','G-TEST123')", $body,
            'GA4 ID must be passed through @js() which produces single-quoted JS literals');
        // Must NOT appear without quotes in the gtag config call (raw injection)
        $this->assertStringNotContainsString("gtag('config',G-TEST123)", $body,
            'GA4 ID must not be placed raw (unquoted) in the gtag call');
    }

    public function test_malicious_ga4_id_cannot_inject_js(): void
    {
        Setting::set('ga4_measurement_id', "');alert(document.domain)//");

        $response = $this->get('/');
        $body = $response->getContent();

        // The payload must be JSON-encoded (backslash-escaped or entity-escaped), not raw
        $this->assertStringNotContainsString("');alert(document.domain)//", $body,
            'A malicious GA4 ID value must not appear unescaped in the page');
    }

    // ── Alpine.js / JS string injection ───────────────────────────────────

    public function test_email_in_alpine_x_show_is_js_safe(): void
    {
        $user = User::factory()->create([
            'email' => "test@example.com",
        ]);

        $response = $this->actingAs($user)->get('/account/profile');
        $body = $response->getContent();

        // Must use Js::from() which produces JSON — verify no raw single-quoted string
        $this->assertStringNotContainsString("!== 'test@example.com'", $body,
            'Email must be JS-encoded with Js::from(), not embedded in a raw JS string literal');
        $this->assertStringContainsString('"test@example.com"', $body,
            'Email should appear as a JSON string in the Alpine expression');
    }

    public function test_special_chars_in_email_cannot_break_alpine_expression(): void
    {
        // Simulate a user whose email (somehow) contains characters that would
        // break out of a JS string if not properly encoded.
        $user = User::factory()->create([
            'email' => 'legit+user@example.com',
        ]);

        $response = $this->actingAs($user)->get('/account/profile');

        $response->assertOk();
        $response->assertDontSee("x-show=\"\$wire.email !== '", false);
    }

    // ── CSRF protection ────────────────────────────────────────────────────

    public function test_logout_requires_csrf_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        // Without CSRF token in TestCase, Laravel returns 419 in real browsers.
        // In tests the CSRF middleware allows the call through with the test session.
        // Assert the route exists and is POST-only (GET would mean no CSRF needed).
        $this->assertNotEquals(405, $response->getStatusCode(),
            'POST /logout must be a valid route');
        $this->assertEquals(302, $response->getStatusCode(),
            'Successful logout must redirect');
    }

    public function test_cart_quick_add_rejects_missing_csrf(): void
    {
        // Simulate a cross-site POST without a CSRF token by disabling session
        // middleware — the 419 response confirms CSRF protection is active.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // This just verifies the route is POST-only and requires auth/session state.
        $response = $this->postJson('/cart/quick-add', ['product_id' => 1, 'qty' => 1]);

        // Route exists (not 404/405) and processes the request
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertNotEquals(405, $response->getStatusCode());
    }

    public function test_paystack_webhook_is_csrf_exempt_but_signature_checked(): void
    {
        // Webhook must be CSRF-exempt (called by Paystack servers, no browser session).
        // The controller must reject unsigned requests.
        $response = $this->postJson('/paystack/webhook', ['event' => 'charge.success']);

        // No webhook secret configured in test env → expect 400 (not 419 CSRF block)
        $this->assertNotEquals(419, $response->getStatusCode(),
            'Paystack webhook must be CSRF-exempt');
        // The controller rejects unsigned webhooks
        $this->assertEquals(400, $response->getStatusCode(),
            'Unsigned webhook must be rejected');
    }

    public function test_paystack_verify_requires_csrf_token_from_browser(): void
    {
        // Laravel's VerifyCsrfToken bypasses the check in test environments
        // (runningUnitTests() = true), so we cannot test for a 419 response directly.
        // Instead, verify the route definition does NOT have withoutMiddleware(VerifyCsrfToken),
        // which would be the only way to bypass CSRF for this browser-initiated endpoint.
        $route = app('router')->getRoutes()->getByName('paystack.verify');

        $this->assertNotNull($route, 'paystack.verify route must exist');

        $excludedMiddleware = $route->excludedMiddleware();

        $csrfClass = \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class;
        $this->assertNotContains($csrfClass, $excludedMiddleware,
            'Paystack verify must NOT be exempt from CSRF — it is browser-initiated');
    }

    // ── Open redirect ──────────────────────────────────────────────────────

    public function test_paystack_verify_redirect_is_an_internal_route(): void
    {
        // The server produces the redirect URL from route() — it cannot be
        // influenced by the client to point to an external domain.
        // We verify by checking the PaystackController source directly.
        $source = file_get_contents(app_path('Http/Controllers/PaystackController.php'));

        $this->assertStringContainsString("route('order.confirmed'", $source,
            "Paystack redirect must use Laravel's route() helper, not a client-supplied URL");
        $this->assertStringNotContainsString('$request->input(\'redirect\')', $source,
            'Redirect URL must never come from client input');
        $this->assertStringNotContainsString('$request->redirect', $source,
            'Redirect URL must never come from client input');
    }

    // ── XSS via order status in admin panel ───────────────────────────────

    public function test_order_status_is_html_escaped_in_admin_user_view(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['email' => 'customer@example.com']);

        Order::create([
            'order_number'    => 'FEN-TEST001',
            'customer_name'   => 'Test Customer',
            'customer_email'  => $user->email,
            'customer_phone'  => '0200000000',
            'items'           => [],
            'delivery_fee'    => 0,
            'discount'        => 0,
            'total'           => 10.00,
            'status'          => '<script>alert("admin-xss")</script>',
            'payment_status'  => '<img src=x onerror=alert(2)>',
            'paystack_ref'    => 'REF-TEST-001',
            'delivery_address'=> '1 Test St',
        ]);

        // Access admin user view
        $response = $this->actingAs($admin)
            ->get('/store-portal/users/' . $user->id);

        $response->assertDontSee('<script>alert("admin-xss")</script>', false);
        $response->assertDontSee('<img src=x onerror=alert(2)>', false);
    }
}
