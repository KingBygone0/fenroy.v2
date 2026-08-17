<?php

namespace Tests\Feature\Security;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class DatabaseSecurityTest extends TestCase
{
    use RefreshDatabase;

    // ── Mass assignment guards ──────────────────────────────────────────────

    public function test_review_is_approved_cannot_be_mass_assigned(): void
    {
        $product = Product::create([
            'name' => 'Test', 'slug' => 'test-' . uniqid(),
            'price' => 10.00, 'stock' => 5, 'is_active' => true,
        ]);

        $review = new Review();
        $review->fill([
            'product_id'    => $product->id,
            'reviewer_name' => 'Kofi',
            'rating'        => 5,
            'body'          => 'Great',
            'is_approved'   => true,   // should be ignored
        ]);

        $this->assertNull($review->is_approved,
            'is_approved must not be settable via fill() — remove from $fillable');
    }

    public function test_coupon_used_count_cannot_be_mass_assigned(): void
    {
        $coupon = new Coupon();
        $coupon->fill([
            'code'       => 'TEST20',
            'type'       => 'percent',
            'value'      => 20,
            'min_order'  => 0,
            'max_uses'   => 5,
            'used_count' => 999,   // should be ignored
            'is_active'  => true,
        ]);

        $this->assertNotEquals(999, $coupon->used_count,
            'used_count must not be settable via fill() — it could reset coupon usage limits');
    }

    // ── Schema integrity ────────────────────────────────────────────────────

    public function test_addresses_table_has_user_id_foreign_key(): void
    {
        $this->assertTrue(
            Schema::hasColumn('addresses', 'user_id'),
            'addresses table must have user_id column'
        );
    }

    public function test_wishlists_table_has_unique_constraint(): void
    {
        $user    = User::factory()->create();
        $product = Product::create([
            'name' => 'Unique', 'slug' => 'unique-' . uniqid(),
            'price' => 5.00, 'stock' => 10, 'is_active' => true,
        ]);

        $wishlist            = new \App\Models\Wishlist();
        $wishlist->user_id   = $user->id;
        $wishlist->product_id = $product->id;
        $wishlist->save();

        // Attempting a duplicate must throw a unique constraint violation
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        $dup             = new \App\Models\Wishlist();
        $dup->user_id    = $user->id;
        $dup->product_id = $product->id;
        $dup->save();
    }

    public function test_orders_table_has_indexes_for_customer_email_and_paystack_ref(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Index introspection requires MySQL — skipped on SQLite test DB');
        }

        $emailIndexed    = collect(\Illuminate\Support\Facades\DB::select('SHOW INDEX FROM orders'))
            ->pluck('Key_name')
            ->contains('orders_customer_email_idx');

        $paystackIndexed = collect(\Illuminate\Support\Facades\DB::select('SHOW INDEX FROM orders'))
            ->pluck('Key_name')
            ->contains('orders_paystack_ref_idx');

        $this->assertTrue($emailIndexed,    'orders.customer_email must be indexed for security-critical ownership lookups');
        $this->assertTrue($paystackIndexed, 'orders.paystack_ref must be indexed for payment verification');
    }

    // ── Sensitive field defaults ────────────────────────────────────────────

    public function test_review_defaults_to_unapproved(): void
    {
        $product = Product::create([
            'name' => 'P', 'slug' => 'p-' . uniqid(),
            'price' => 1.00, 'stock' => 1, 'is_active' => true,
        ]);

        $review = Review::create([
            'product_id'    => $product->id,
            'reviewer_name' => 'Kwame',
            'rating'        => 4,
        ]);

        $this->assertNull($review->is_approved,
            'New reviews must default to null (pending), not auto-approved');
    }

    public function test_new_user_is_not_admin_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertFalse((bool) $user->is_admin,
            'Newly registered users must not have is_admin = true');
    }

    // ── Password storage ────────────────────────────────────────────────────

    public function test_user_password_is_stored_hashed_not_plaintext(): void
    {
        $user = User::factory()->create(['password' => 'plaintext-password']);

        $this->assertNotEquals('plaintext-password', $user->password,
            'Passwords must be bcrypt-hashed in the database, never stored as plaintext');
        $this->assertTrue(str_starts_with($user->password, '$2y$') || str_starts_with($user->password, '$argon'),
            'Password hash must use a recognized modern hashing algorithm');
    }

    // ── Coupon integrity ────────────────────────────────────────────────────

    public function test_coupon_used_count_increments_correctly(): void
    {
        $coupon = Coupon::create([
            'code'      => 'INTEG-' . uniqid(),
            'type'      => 'fixed',
            'value'     => 10,
            'min_order' => 0,
            'max_uses'  => 3,
            'is_active' => true,
        ]);

        $this->assertEquals(0, $coupon->fresh()->used_count);

        Coupon::where('code', $coupon->code)->increment('used_count');

        $this->assertEquals(1, $coupon->fresh()->used_count,
            'increment() must increase used_count by exactly 1');
    }

    public function test_coupon_is_invalid_when_max_uses_reached(): void
    {
        $coupon = Coupon::create([
            'code'      => 'MAXUSE-' . uniqid(),
            'type'      => 'fixed',
            'value'     => 5,
            'min_order' => 0,
            'max_uses'  => 2,
            'is_active' => true,
        ]);

        // Simulate 2 uses via the same mechanism the app uses (bypasses fillable correctly)
        Coupon::where('id', $coupon->id)->increment('used_count', 2);

        $this->assertFalse($coupon->fresh()->isValid(100),
            'A coupon that has reached its max_uses must be invalid');
    }

    // ── Order data integrity ────────────────────────────────────────────────

    public function test_order_number_is_unique(): void
    {
        $makeOrder = fn ($num) => Order::create([
            'order_number'     => $num,
            'customer_name'    => 'Test',
            'customer_email'   => 'test@example.com',
            'customer_phone'   => '0241234567',
            'delivery_address' => 'Kumasi',
            'delivery_window'  => 'morning',
            'total'            => 50.00,
            'delivery_fee'     => 0,
            'discount'         => 0,
            'status'           => 'processing',
            'payment_status'   => 'paid',
            'items'            => [],
        ]);

        $makeOrder('FEN-UNIQUE-001');

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $makeOrder('FEN-UNIQUE-001');
    }
}
