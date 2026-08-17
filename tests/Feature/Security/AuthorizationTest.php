<?php

namespace Tests\Feature\Security;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeOrder(User $user, array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number'     => 'FEN-' . strtoupper(uniqid()),
            'customer_name'    => $user->name,
            'customer_email'   => $user->email,
            'customer_phone'   => '0241234567',
            'delivery_address' => 'Kumasi',
            'delivery_window'  => 'morning',
            'total'            => 100.00,
            'delivery_fee'     => 10.00,
            'discount'         => 0,
            'status'           => 'processing',
            'payment_status'   => 'paid',
            'items'            => [],  // cast handles JSON encoding
        ], $attrs));
    }

    private function makeAddress(User $user): Address
    {
        $address          = new Address();
        $address->user_id = $user->id;
        $address->fill([
            'full_name'  => $user->name,
            'phone'      => '0241234567',
            'line1'      => '1 Test Street',
            'city'       => 'Kumasi',
            'region'     => 'Ashanti',
            'is_default' => true,
        ]);
        $address->save();
        return $address;
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'name'      => 'Test Product',
            'slug'      => 'test-product-' . uniqid(),
            'price'     => 25.00,
            'stock'     => 50,
            'is_active' => true,
        ]);
    }

    // ── Order IDOR — /order-confirmed ─────────────────────────────────────

    public function test_anonymous_user_cannot_see_another_persons_order_details(): void
    {
        $owner = User::factory()->create();
        $order = $this->makeOrder($owner);

        $response = $this->get(route('order.confirmed', ['orderNumber' => $order->order_number]));

        // Must not expose PII
        $response->assertDontSee($owner->email);
        $response->assertDontSee($owner->name);
    }

    public function test_authenticated_user_cannot_see_another_users_order_on_confirmation(): void
    {
        $owner     = User::factory()->create(['email' => 'owner@example.com']);
        $attacker  = User::factory()->create(['email' => 'attacker@example.com']);
        $order     = $this->makeOrder($owner);

        $response = $this->actingAs($attacker)
            ->get(route('order.confirmed', ['orderNumber' => $order->order_number]));

        $response->assertDontSee($owner->email);
    }

    public function test_owner_can_see_their_own_order_confirmation(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $order = $this->makeOrder($owner);

        $response = $this->actingAs($owner)
            ->get(route('order.confirmed', ['orderNumber' => $order->order_number]));

        $response->assertSuccessful();
        $response->assertSee($order->order_number);
    }

    // ── Order IDOR — /order/track ────────────────────────────────────────

    public function test_authenticated_user_cannot_track_another_users_order(): void
    {
        $owner    = User::factory()->create(['email' => 'victim@example.com']);
        $attacker = User::factory()->create(['email' => 'attacker@example.com']);
        $order    = $this->makeOrder($owner);

        $response = $this->actingAs($attacker)
            ->get(route('order.track', ['orderNumber' => $order->order_number]));

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_order_tracking(): void
    {
        $owner = User::factory()->create();
        $order = $this->makeOrder($owner);

        $response = $this->get(route('order.track', ['orderNumber' => $order->order_number]));

        $response->assertRedirect(route('login'));
    }

    public function test_owner_can_track_their_own_order(): void
    {
        $owner = User::factory()->create();
        $order = $this->makeOrder($owner);

        $response = $this->actingAs($owner)
            ->get(route('order.track', ['orderNumber' => $order->order_number]));

        $response->assertSuccessful();
    }

    public function test_nonexistent_order_number_returns_404_not_info_leak(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('order.track', ['orderNumber' => 'FEN-DOESNOTEXIST']));

        $response->assertStatus(404);
    }

    // ── Address IDOR ──────────────────────────────────────────────────────

    public function test_user_cannot_delete_another_users_address(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $address  = $this->makeAddress($owner);

        Livewire::actingAs($attacker)
            ->test(\App\Livewire\AccountAddresses::class)
            ->call('delete', $address->id);

        // Address must still exist
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_user_cannot_set_another_users_address_as_default(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $address  = $this->makeAddress($owner);

        Livewire::actingAs($attacker)
            ->test(\App\Livewire\AccountAddresses::class)
            ->call('setDefault', $address->id);

        // Owner's address must not have been touched by attacker
        $address->refresh();
        $this->assertTrue((bool) $address->is_default, 'Original address default flag must be unchanged');
    }

    public function test_address_user_id_is_always_authenticated_user(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\AccountAddresses::class)
            ->set('full_name', 'Kofi Test')
            ->set('phone', '0241234567')
            ->set('line1', '5 Main Street')
            ->set('city', 'Kumasi')
            ->set('region', 'Ashanti')
            ->call('save');

        $address = Address::where('full_name', 'Kofi Test')->first();
        $this->assertNotNull($address);
        $this->assertEquals($user->id, $address->user_id,
            'Address must belong to the authenticated user, not a client-supplied ID');
    }

    // ── Mass assignment — user_id on Address/Wishlist ─────────────────────

    public function test_address_user_id_cannot_be_mass_assigned(): void
    {
        $victim = User::factory()->create();

        $address = new Address();
        // Try filling user_id via fill() — it must be ignored because it's not in $fillable
        $address->fill(['user_id' => $victim->id, 'full_name' => 'Hijacked']);

        $this->assertNull($address->user_id,
            'user_id must not be settable via fill() / mass assignment on Address');
    }

    public function test_wishlist_user_id_cannot_be_mass_assigned(): void
    {
        $victim  = User::factory()->create();
        $product = $this->makeProduct();

        $wishlist = new Wishlist();
        $wishlist->fill(['user_id' => $victim->id, 'product_id' => $product->id]);

        $this->assertNull($wishlist->user_id,
            'user_id must not be settable via fill() / mass assignment on Wishlist');
    }

    // ── Vertical privilege escalation — admin routes ───────────────────────

    public function test_regular_user_cannot_access_admin_import_endpoint(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->postJson('/admin/import-products/upload', [
            'file' => base64_encode('fake,csv,data'),
            'name' => 'test.csv',
            'ext'  => 'csv',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_import_endpoint(): void
    {
        $response = $this->postJson('/admin/import-products/upload', [
            'file' => base64_encode('fake'),
            'name' => 'x.csv',
            'ext'  => 'csv',
        ]);

        // Unauthenticated requests are redirected (web) or 401 (JSON) — never 200
        $this->assertContains(
            $response->getStatusCode(),
            [401, 302, 403],
            'Unauthenticated request must not succeed'
        );
    }

    public function test_regular_user_cannot_access_filament_admin_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/store-portal');

        $this->assertNotEquals(200, $response->getStatusCode());
    }

    // ── Account orders scoped to authenticated user ────────────────────────

    public function test_orders_livewire_component_only_returns_authenticated_users_orders(): void
    {
        $userA = User::factory()->create(['email' => 'usera@example.com']);
        $userB = User::factory()->create(['email' => 'userb@example.com']);

        $orderA = $this->makeOrder($userA);
        $orderB = $this->makeOrder($userB);

        $component = Livewire::actingAs($userA)
            ->test(\App\Livewire\AccountOrders::class);

        $orders = $component->get('tab'); // force render
        $rendered = $component->html();

        $this->assertStringNotContainsString($orderB->order_number, $rendered,
            "User A's orders view must not contain User B's order number");
    }

    // ── Wishlist scoped to authenticated user ──────────────────────────────

    public function test_wishlist_only_shows_authenticated_users_items(): void
    {
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();
        $product = $this->makeProduct();

        $wishlist             = new Wishlist();
        $wishlist->user_id    = $userB->id;
        $wishlist->product_id = $product->id;
        $wishlist->save();

        $component = Livewire::actingAs($userA)
            ->test(\App\Livewire\AccountWishlist::class);

        $items = $component->get('items');
        $this->assertEmpty($items, "User A's wishlist must not contain User B's items");
    }
}
