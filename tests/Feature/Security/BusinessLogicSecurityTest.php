<?php

namespace Tests\Feature\Security;

use App\Http\Controllers\PaystackController;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class BusinessLogicSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function makeZone(float $fee = 10.00, ?float $freeAbove = null): DeliveryZone
    {
        return DeliveryZone::create([
            'name'       => 'Test Zone',
            'areas'      => ['Test Area'],
            'fee'        => $fee,
            'free_above' => $freeAbove,
            'sort_order' => 1,
            'is_active'  => true,
        ]);
    }

    private function makeProduct(string $slug, float $price, int $stock = 100, bool $active = true): Product
    {
        return Product::create([
            'name'      => ucfirst($slug),
            'slug'      => $slug,
            'price'     => $price,
            'stock'     => $stock,
            'is_active' => $active,
            'category'  => 'test',
        ]);
    }

    private function makeCoupon(string $type, float $value, int $maxUses = 100, float $minOrder = 0): Coupon
    {
        return Coupon::create([
            'code'      => 'TEST' . strtoupper(uniqid()),
            'type'      => $type,
            'value'     => $value,
            'min_order' => $minOrder,
            'max_uses'  => $maxUses,
            'is_active' => true,
        ]);
    }

    private function fakePaystackSuccess(float $amountGhs): void
    {
        Http::fake([
            '*/transaction/verify/*' => Http::response([
                'data' => [
                    'status' => 'success',
                    'amount' => (int) round($amountGhs * 100),
                ],
            ], 200),
        ]);
    }

    // ── VULN-1: Stale coupon discount after cart shrinks ──────────────────────
    // A percent coupon computed on a large cart must NOT be re-applied unchanged
    // when items are later removed. The recalculated discount must match the
    // current (smaller) subtotal, not the original.

    public function test_coupon_discount_is_recalculated_from_current_subtotal_not_session(): void
    {
        $zone    = $this->makeZone();
        $product = $this->makeProduct('banana', 100.00);
        $coupon  = $this->makeCoupon('percent', 10); // 10% off

        // Apply coupon while cart has GH₵ 500 → discount = GH₵ 50 stored in session
        $this->withSession([
            'cart_items'    => [['slug' => 'banana', 'name' => 'Banana', 'price' => 100.00, 'qty' => 5, 'unit' => '1']],
            'cart_coupon'   => $coupon->code,
            'cart_discount' => 50.00, // stale: computed when cart was GH₵ 500
        ]);

        // User removes 4 items — cart is now GH₵ 100 — and proceeds to checkout
        $this->withSession([
            'cart_items'    => [['slug' => 'banana', 'name' => 'Banana', 'price' => 100.00, 'qty' => 1, 'unit' => '1']],
            'cart_coupon'   => $coupon->code,
            'cart_discount' => 50.00, // stale value is still in session
        ]);

        // placeOrder() must recalculate: 10% of GH₵ 100 = GH₵ 10, not GH₵ 50
        $user = User::factory()->create();
        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder, 'placeOrder() should build pending_order');
        // Discount must be 10% of GH₵ 100 = GH₵ 10, NOT the stale GH₵ 50
        $this->assertEquals(10.00, $pendingOrder['discount'],
            'Coupon discount must be recalculated from current cart subtotal, not from session');
        // Total must be GH₵ 100 + GH₵ 10 delivery - GH₵ 10 discount = GH₵ 100
        $this->assertGreaterThan(0, $pendingOrder['total'],
            'Total must be positive after correct discount calculation');
    }

    public function test_stale_fixed_coupon_cannot_exceed_current_cart_value(): void
    {
        $zone    = $this->makeZone(fee: 0);
        $product = $this->makeProduct('milo', 50.00);
        // Fixed GH₵ 200 coupon applied when cart was GH₵ 500; now cart is only GH₵ 50
        $coupon  = $this->makeCoupon('fixed', 200, minOrder: 50);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items'    => [['slug' => 'milo', 'name' => 'Milo', 'price' => 50.00, 'qty' => 1, 'unit' => '1']],
            'cart_coupon'   => $coupon->code,
            'cart_discount' => 200.00, // stale — impossible discount on current cart
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder);
        // Coupon::discountFor() caps fixed coupons at the subtotal → GH₵ 50 max
        $this->assertLessThanOrEqual(50.00, $pendingOrder['discount'],
            'Fixed coupon discount must be capped at current cart subtotal');
        // Total must never go below zero
        $this->assertGreaterThanOrEqual(0, $pendingOrder['total'],
            'Order total must never be negative');
    }

    // ── VULN-2: Revoked coupon must not be honoured at payment time ───────────

    public function test_expired_coupon_in_session_is_rejected_at_checkout(): void
    {
        $zone    = $this->makeZone();
        $product = $this->makeProduct('soap', 80.00);
        $coupon  = $this->makeCoupon('fixed', 20);
        // Expire the coupon after it was applied
        $coupon->update(['expires_at' => now()->subHour()]);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items'    => [['slug' => 'soap', 'name' => 'Soap', 'price' => 80.00, 'qty' => 1, 'unit' => '1']],
            'cart_coupon'   => $coupon->code,
            'cart_discount' => 20.00,
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder);
        $this->assertEquals(0, $pendingOrder['discount'],
            'Expired coupon must not grant a discount at checkout time');
        $this->assertNull($pendingOrder['coupon_code'],
            'Expired coupon code must be cleared from pending order');
    }

    public function test_deactivated_coupon_in_session_is_rejected_at_checkout(): void
    {
        $zone    = $this->makeZone();
        $product = $this->makeProduct('milk', 60.00);
        $coupon  = $this->makeCoupon('percent', 15);
        $coupon->update(['is_active' => false]);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items'    => [['slug' => 'milk', 'name' => 'Milk', 'price' => 60.00, 'qty' => 1, 'unit' => '1']],
            'cart_coupon'   => $coupon->code,
            'cart_discount' => 9.00,
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder);
        $this->assertEquals(0, $pendingOrder['discount'],
            'Deactivated coupon must not grant a discount');
    }

    public function test_maxed_out_coupon_is_rejected_at_payment_time(): void
    {
        $zone    = $this->makeZone();
        $product = $this->makeProduct('noodles', 28.00);
        // Single-use coupon already exhausted
        $coupon  = $this->makeCoupon('fixed', 5, maxUses: 1);
        $coupon->increment('used_count'); // now used_count = max_uses

        $user = User::factory()->create();
        $this->withSession([
            'cart_items'    => [['slug' => 'noodles', 'name' => 'Noodles', 'price' => 28.00, 'qty' => 1, 'unit' => '1']],
            'cart_coupon'   => $coupon->code,
            'cart_discount' => 5.00,
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder);
        $this->assertEquals(0, $pendingOrder['discount'],
            'A single-use coupon already fully used must not grant further discounts');
    }

    // ── VULN-3: Concurrent verify() must not create duplicate orders ──────────

    public function test_duplicate_verify_call_does_not_create_two_orders(): void
    {
        $coupon = $this->makeCoupon('fixed', 10, maxUses: 1);

        $orderData = [
            'name'            => 'Race User',
            'email'           => 'race@example.com',
            'phone'           => '0200000000',
            'address'         => '1 Race St',
            'delivery_zone'   => 'Test Zone',
            'delivery_window' => 'Today, 8am – 12pm',
            'items'           => [['slug' => 'p1', 'name' => 'P1', 'price' => 100, 'qty' => 1]],
            'subtotal'        => 100.00,
            'delivery_fee'    => 10.00,
            'discount'        => 10.00,
            'coupon_code'     => $coupon->code,
            'total'           => 100.00,
        ];

        $this->fakePaystackSuccess(100.00);

        $ref = 'FEN-RACETEST001';

        // First call — creates the order
        $response1 = $this->withSession(['pending_order' => $orderData])
            ->postJson('/paystack/verify', ['reference' => $ref]);

        $response1->assertOk();
        $this->assertEquals(1, Order::where('paystack_ref', $ref)->count(),
            'First verify() call should create exactly one order');

        // Second call with same reference — must return the existing order, not create another
        $response2 = $this->withSession(['pending_order' => $orderData])
            ->postJson('/paystack/verify', ['reference' => $ref]);

        $response2->assertOk();
        $this->assertEquals(1, Order::where('paystack_ref', $ref)->count(),
            'Second verify() call with same reference must NOT create a second order');

        // Coupon must only be incremented once
        $coupon->refresh();
        $this->assertEquals(1, $coupon->used_count,
            'Coupon used_count must be incremented exactly once, not twice');
    }

    // ── VULN-4: Deactivated / out-of-stock products must be rejected ──────────

    public function test_deactivated_product_in_cart_is_dropped_at_checkout(): void
    {
        $zone     = $this->makeZone();
        $active   = $this->makeProduct('active-product', 50.00, active: true);
        $inactive = $this->makeProduct('inactive-product', 100.00, active: false);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items' => [
                ['slug' => 'active-product',   'name' => 'Active',   'price' => 50.00,  'qty' => 1, 'unit' => '1'],
                ['slug' => 'inactive-product', 'name' => 'Inactive', 'price' => 100.00, 'qty' => 1, 'unit' => '1'],
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder, 'Order should proceed with available items');

        $slugsInOrder = array_column($pendingOrder['items'], 'slug');
        $this->assertContains('active-product', $slugsInOrder,
            'Active product must be in the order');
        $this->assertNotContains('inactive-product', $slugsInOrder,
            'Deactivated product must be dropped from the order');

        // Price must come from DB, not client session
        $itemPrice = collect($pendingOrder['items'])
            ->firstWhere('slug', 'active-product')['price'] ?? null;
        $this->assertEquals(50.00, $itemPrice,
            'Order must use the current DB price, not the session price');
    }

    public function test_out_of_stock_product_is_dropped_from_checkout(): void
    {
        $zone     = $this->makeZone();
        $inStock  = $this->makeProduct('in-stock', 30.00, stock: 5);
        $noStock  = $this->makeProduct('no-stock', 40.00, stock: 0);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items' => [
                ['slug' => 'in-stock',  'name' => 'In Stock',  'price' => 30.00, 'qty' => 2, 'unit' => '1'],
                ['slug' => 'no-stock',  'name' => 'No Stock',  'price' => 40.00, 'qty' => 1, 'unit' => '1'],
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder);

        $slugsInOrder = array_column($pendingOrder['items'], 'slug');
        $this->assertContains('in-stock', $slugsInOrder);
        $this->assertNotContains('no-stock', $slugsInOrder,
            'Zero-stock product must be dropped from the order');
    }

    public function test_quantity_is_capped_to_available_stock(): void
    {
        $zone    = $this->makeZone();
        $product = $this->makeProduct('limited', 20.00, stock: 3);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items' => [
                ['slug' => 'limited', 'name' => 'Limited', 'price' => 20.00, 'qty' => 10, 'unit' => '1'],
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder);

        $item = collect($pendingOrder['items'])->firstWhere('slug', 'limited');
        $this->assertNotNull($item);
        $this->assertLessThanOrEqual(3, $item['qty'],
            'Quantity must be capped to available stock (3), not the cart quantity (10)');
    }

    // ── VULN-5: Cart prices must be re-validated from DB at checkout ──────────

    public function test_price_change_since_add_to_cart_is_reflected_in_order(): void
    {
        $zone    = $this->makeZone(fee: 0);
        $product = $this->makeProduct('apple', 10.00); // Original price GH₵ 10

        // Admin raises price to GH₵ 25 after user added it at GH₵ 10
        $product->update(['price' => 25.00]);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items' => [
                ['slug' => 'apple', 'name' => 'Apple', 'price' => 10.00, 'qty' => 2, 'unit' => '1'],
                // Session still has old price GH₵ 10
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $pendingOrder = session('pending_order');
        $this->assertNotNull($pendingOrder);

        $item = collect($pendingOrder['items'])->firstWhere('slug', 'apple');
        $this->assertEquals(25.00, $item['price'],
            'Order must use the current DB price (GH₵ 25), not the stale session price (GH₵ 10)');
        $this->assertEquals(50.00, $pendingOrder['subtotal'],
            'Subtotal must be calculated from the current DB price');
    }

    // ── VULN-6: Empty / fallback-only cart must be blocked ───────────────────

    public function test_checkout_requires_non_empty_real_cart(): void
    {
        $zone = $this->makeZone();

        $user = User::factory()->create();
        // No cart_items in session — falls back to demo items in old code
        $this->withSession([]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        $this->assertNull(session('pending_order'),
            'Empty cart must not produce a pending order (no fallback demo items in real orders)');
    }

    // ── Order ownership / IDOR ─────────────────────────────────────────────────

    public function test_order_confirmed_page_hides_details_from_other_users(): void
    {
        $victim = User::factory()->create(['email' => 'victim@example.com']);
        $attacker = User::factory()->create(['email' => 'attacker@example.com']);

        $order = Order::create([
            'order_number'    => 'FEN-VICTIM001',
            'customer_name'   => 'Victim',
            'customer_email'  => $victim->email,
            'customer_phone'  => '0200000001',
            'items'           => [],
            'delivery_fee'    => 0,
            'discount'        => 0,
            'total'           => 100.00,
            'status'          => 'processing',
            'payment_status'  => 'paid',
            'paystack_ref'    => 'REF-VICTIM',
            'delivery_address'=> '1 Victim St',
        ]);

        // Attacker tries to view victim's order by guessing the order number
        $response = $this->actingAs($attacker)
            ->get('/order-confirmed/' . $order->order_number);

        $response->assertOk();
        // The order number may appear in the URL/meta tags (it's in the URL bar), but
        // confidential order details must be hidden from a different user.
        $response->assertDontSee('victim@example.com', false);
        $response->assertDontSee('1 Victim St', false); // delivery address must not leak
    }

    public function test_order_tracking_is_restricted_to_owner(): void
    {
        $victim   = User::factory()->create(['email' => 'victim2@example.com']);
        $attacker = User::factory()->create(['email' => 'attacker2@example.com']);

        $order = Order::create([
            'order_number'    => 'FEN-VICTIM002',
            'customer_name'   => 'Victim',
            'customer_email'  => $victim->email,
            'customer_phone'  => '0200000001',
            'items'           => [],
            'delivery_fee'    => 0,
            'discount'        => 0,
            'total'           => 100.00,
            'status'          => 'processing',
            'payment_status'  => 'paid',
            'paystack_ref'    => 'REF-VICTIM2',
            'delivery_address'=> '1 Victim St',
        ]);

        $this->actingAs($attacker)
            ->get('/order/track/' . $order->order_number)
            ->assertNotFound();
    }

    // ── Webhook idempotency / replay ───────────────────────────────────────────

    public function test_webhook_replay_does_not_double_increment_coupon(): void
    {
        $coupon = $this->makeCoupon('fixed', 10, maxUses: 10);

        // Simulate an order already created and marked paid by verify()
        $order = Order::create([
            'order_number'    => 'FEN-WEBHOOK001',
            'customer_name'   => 'Webhook User',
            'customer_email'  => 'wh@example.com',
            'customer_phone'  => '0200000000',
            'items'           => [],
            'delivery_fee'    => 10.00,
            'discount'        => 10.00,
            'total'           => 90.00,
            'status'          => 'processing',
            'payment_status'  => 'paid', // already paid — verify() ran first
            'paystack_ref'    => 'REF-WH001',
            'coupon_code'     => $coupon->code,
            'delivery_address'=> '1 Test St',
        ]);

        $coupon->increment('used_count'); // verify() already incremented this
        $coupon->refresh();
        $initialCount = $coupon->used_count;

        $webhookSecret = 'test-webhook-secret';
        config(['paystack.webhook_secret' => $webhookSecret]);

        $payload = json_encode([
            'event' => 'charge.success',
            'data'  => ['reference' => 'REF-WH001'],
        ]);
        $signature = hash_hmac('sha512', $payload, $webhookSecret);

        $this->postJson('/paystack/webhook', json_decode($payload, true), [
            'X-Paystack-Signature' => $signature,
        ])->assertOk();

        // Webhook must not re-increment because order was already paid
        $coupon->refresh();
        $this->assertEquals($initialCount, $coupon->used_count,
            'Webhook replay must not double-increment coupon used_count');
    }

    public function test_unsigned_webhook_is_rejected(): void
    {
        $this->postJson('/paystack/webhook', ['event' => 'charge.success'])
            ->assertStatus(400);
    }

    // ── Role escalation ────────────────────────────────────────────────────────

    public function test_is_admin_cannot_be_set_via_profile_update(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Attempt to elevate privileges via AccountProfile save() — is_admin is not
        // in User::$fillable and the Livewire component only saves name/email/phone/avatar
        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\AccountProfile::class);
        $component->set('name', 'Hacker')->call('save');

        $user->refresh();
        $this->assertFalse((bool) $user->is_admin,
            'Profile save must not grant admin privileges');
    }

    public function test_admin_panel_blocks_non_admin_users(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Filament 5 returns 403 for authenticated but unauthorised users
        $this->actingAs($user)->get('/store-portal')
            ->assertForbidden();
    }

    // ── Quick-add uses DB price, not client-supplied price ────────────────────

    public function test_quick_add_ignores_client_supplied_price(): void
    {
        $this->makeProduct('mango', 15.00);

        // Attacker sends price=0.01 in the request body
        $response = $this->postJson('/cart/quick-add', [
            'slug'  => 'mango',
            'price' => 0.01, // attempted price manipulation
            'name'  => 'Mango',
            'qty'   => 1,
        ]);

        $response->assertOk();

        $cartItems = session('cart_items');
        $item      = collect($cartItems)->firstWhere('slug', 'mango');

        $this->assertNotNull($item);
        $this->assertEquals(15.00, $item['price'],
            'Cart must use the server-side DB price (15.00), not the client-supplied price (0.01)');
    }

    public function test_quick_add_rejects_non_existent_product(): void
    {
        $this->postJson('/cart/quick-add', ['slug' => 'totally-fake-product'])
            ->assertStatus(404);
    }

    public function test_quick_add_rejects_inactive_product(): void
    {
        $this->makeProduct('hidden-item', 99.00, active: false);

        $this->postJson('/cart/quick-add', ['slug' => 'hidden-item'])
            ->assertStatus(404);
    }

    // ── NEW FINDING 1: Email uniqueness must be enforced on profile update ────

    public function test_profile_update_cannot_steal_another_users_email(): void
    {
        $victim   = User::factory()->create(['email' => 'taken@example.com']);
        $attacker = User::factory()->create(['email' => 'attacker@example.com']);

        $component = \Livewire\Livewire::actingAs($attacker)->test(\App\Livewire\AccountProfile::class);
        $component->set('name', 'Attacker')
            ->set('email', 'taken@example.com') // another user's email
            ->call('save');

        // Must produce a validation error on the email field
        $component->assertHasErrors(['email']);

        // Attacker's email must NOT have changed
        $attacker->refresh();
        $this->assertEquals('attacker@example.com', $attacker->email,
            'Attacker must not be able to steal victim\'s email via profile update');

        // Victim's email must remain unchanged
        $victim->refresh();
        $this->assertEquals('taken@example.com', $victim->email,
            'Victim email must remain unchanged after attacker profile update attempt');
    }

    public function test_profile_update_allows_keeping_own_email(): void
    {
        $user = User::factory()->create(['email' => 'myemail@example.com']);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\AccountProfile::class);
        $component->set('name', 'My Name')
            ->set('email', 'myemail@example.com') // same as their current email
            ->call('save');

        // Saving the same email must not produce a unique constraint error
        $component->assertHasNoErrors(['email']);
    }

    // ── NEW FINDING 2: Coupon enumeration rate limit ──────────────────────────

    public function test_coupon_application_is_rate_limited(): void
    {
        // Exhaust the 10-per-minute limit
        for ($i = 0; $i < 10; $i++) {
            $component = \Livewire\Livewire::test(\App\Livewire\CartPage::class);
            $component->set('couponInput', 'GUESS' . $i)->call('applyCoupon');
        }

        // The 11th attempt must be blocked by the rate limiter
        $component = \Livewire\Livewire::test(\App\Livewire\CartPage::class);
        $component->set('couponInput', 'GUESS11')->call('applyCoupon');

        $component->assertSet('couponError', 'Too many attempts. Please wait before trying again.');
    }

    public function test_coupon_error_does_not_reveal_whether_code_exists(): void
    {
        // Valid but min_order not met — previously revealed min_order amount
        $coupon = $this->makeCoupon('fixed', 20, minOrder: 500.00);

        $component = \Livewire\Livewire::test(\App\Livewire\CartPage::class);
        $component->set('couponInput', $coupon->code)->call('applyCoupon');

        $error = $component->get('couponError');
        // Must NOT reveal the minimum order amount (information leak)
        $this->assertStringNotContainsString('500', $error,
            'Error message must not reveal the coupon minimum order threshold');
        // Must be the generic message
        $this->assertEquals('This code is not valid or could not be applied.', $error);
    }

    // ── NEW FINDING 3: Stock must be decremented when order is created ────────

    public function test_stock_is_decremented_after_successful_payment(): void
    {
        $product = $this->makeProduct('stock-test', 50.00, stock: 10);

        $orderData = [
            'name'            => 'Stock Tester',
            'email'           => 'stock@example.com',
            'phone'           => '0200000000',
            'address'         => '1 Stock St',
            'delivery_zone'   => 'Test Zone',
            'delivery_window' => 'Today, 8am – 12pm',
            'items'           => [['slug' => 'stock-test', 'name' => 'Stock Test', 'price' => 50.00, 'qty' => 3]],
            'subtotal'        => 150.00,
            'delivery_fee'    => 10.00,
            'discount'        => 0,
            'coupon_code'     => null,
            'total'           => 160.00,
        ];

        $this->fakePaystackSuccess(160.00);

        $this->withSession(['pending_order' => $orderData])
            ->postJson('/paystack/verify', ['reference' => 'FEN-STOCKTEST001'])
            ->assertOk();

        $product->refresh();
        $this->assertEquals(7, $product->stock,
            'Stock must be decremented by ordered quantity (10 - 3 = 7)');
    }

    public function test_stock_does_not_go_below_zero(): void
    {
        $product = $this->makeProduct('scarce-item', 30.00, stock: 2);

        $orderData = [
            'name'            => 'Oversell Tester',
            'email'           => 'oversell@example.com',
            'phone'           => '0200000000',
            'address'         => '1 Test St',
            'delivery_zone'   => 'Test Zone',
            'delivery_window' => 'Today, 8am – 12pm',
            'items'           => [['slug' => 'scarce-item', 'name' => 'Scarce', 'price' => 30.00, 'qty' => 5]],
            'subtotal'        => 150.00,
            'delivery_fee'    => 0,
            'discount'        => 0,
            'coupon_code'     => null,
            'total'           => 150.00,
        ];

        $this->fakePaystackSuccess(150.00);

        $this->withSession(['pending_order' => $orderData])
            ->postJson('/paystack/verify', ['reference' => 'FEN-OVERSELL001'])
            ->assertOk();

        $product->refresh();
        $this->assertGreaterThanOrEqual(0, $product->stock,
            'Stock must never go below zero — must be capped at 0');
    }

    // ── NEW FINDING 4: Minimum order amount must be enforced server-side ──────

    public function test_checkout_enforces_minimum_order_amount(): void
    {
        \App\Models\Setting::set('minimum_order_amount', '100');

        $zone    = $this->makeZone();
        $product = $this->makeProduct('cheap-item', 10.00);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items' => [
                ['slug' => 'cheap-item', 'name' => 'Cheap', 'price' => 10.00, 'qty' => 1, 'unit' => '1'],
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        // Order must NOT proceed — subtotal GH₵ 10 < minimum GH₵ 100
        $this->assertNull(session('pending_order'),
            'Order below minimum order amount must be blocked at checkout');
    }

    public function test_checkout_allows_order_meeting_minimum_amount(): void
    {
        \App\Models\Setting::set('minimum_order_amount', '50');

        $zone    = $this->makeZone(fee: 0);
        $product = $this->makeProduct('good-item', 60.00);

        $user = User::factory()->create();
        $this->withSession([
            'cart_items' => [
                ['slug' => 'good-item', 'name' => 'Good', 'price' => 60.00, 'qty' => 1, 'unit' => '1'],
            ],
        ]);

        $component = \Livewire\Livewire::actingAs($user)->test(\App\Livewire\CheckoutPage::class);
        $component->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', '0200000000')
            ->set('address', '1 Test Street, Accra')
            ->set('zoneId', $zone->id)
            ->call('placeOrder');

        // Subtotal GH₵ 60 >= minimum GH₵ 50 — order should proceed
        $this->assertNotNull(session('pending_order'),
            'Order meeting minimum order amount must be allowed to proceed');
    }

    // ── NEW FINDING 6: order-confirmed page must not reveal order details ─────

    public function test_order_confirmed_page_shows_only_generic_ui_for_unauthenticated_visitor(): void
    {
        $victim = User::factory()->create(['email' => 'victim3@example.com']);

        $order = Order::create([
            'order_number'    => 'FEN-VICTIM003',
            'customer_name'   => 'Victim User',
            'customer_email'  => $victim->email,
            'customer_phone'  => '0200000003',
            'items'           => [],
            'delivery_fee'    => 0,
            'discount'        => 0,
            'total'           => 200.00,
            'status'          => 'processing',
            'payment_status'  => 'paid',
            'paystack_ref'    => 'REF-VICTIM3',
            'delivery_address'=> '99 Secret Lane',
        ]);

        // Unauthenticated visitor knows the order number
        $response = $this->get('/order-confirmed/' . $order->order_number);

        $response->assertOk();
        // Must not expose sensitive order details
        $response->assertDontSee('victim3@example.com', false);
        $response->assertDontSee('99 Secret Lane', false);
        // Must not render the delivery card, items, or status timeline for unknown viewer
        $response->assertDontSee('Delivery window', false);
    }
}
