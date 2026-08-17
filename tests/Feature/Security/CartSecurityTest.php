<?php

namespace Tests\Feature\Security;

use App\Models\Coupon;
use App\Models\Product;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name'      => 'Test Product',
            'slug'      => 'test-product-' . uniqid(),
            'price'     => 50.00,
            'stock'     => 100,
            'is_active' => true,
        ], $attrs));
    }

    private function createCoupon(array $attrs = []): Coupon
    {
        return Coupon::create(array_merge([
            'code'      => 'TESTCODE' . strtoupper(uniqid()),
            'type'      => 'fixed',
            'value'     => 5.00,
            'min_order' => 0,
            'is_active' => true,
        ], $attrs));
    }

    public function test_quick_add_uses_db_price_not_client_price(): void
    {
        $this->createProduct(['slug' => 'price-test-product', 'price' => 99.99]);

        $response = $this->postJson('/cart/quick-add', [
            'slug'  => 'price-test-product',
            'price' => 0.01,
            'name'  => 'Hacked Name',
        ]);

        $response->assertSuccessful();

        $cartItems = session('cart_items', []);
        $item      = collect($cartItems)->firstWhere('slug', 'price-test-product');

        $this->assertNotNull($item);
        $this->assertEquals(99.99, $item['price'], 'Cart must use DB price, not client-supplied price');
    }

    public function test_quick_add_rejects_unknown_product(): void
    {
        $response = $this->postJson('/cart/quick-add', [
            'slug' => 'nonexistent-product',
        ]);

        $response->assertStatus(404);
    }

    public function test_coupon_error_does_not_reveal_expiry_details(): void
    {
        $this->createCoupon([
            'code'       => 'EXPIRED10',
            'is_active'  => true,
            'expires_at' => now()->subDay(),
        ]);

        $component = Livewire::test(\App\Livewire\CartPage::class)
            ->set('couponInput', 'EXPIRED10')
            ->call('applyCoupon');

        $error = $component->get('couponError');

        // Must not reveal whether coupon expired, was used up, or simply doesn't exist
        $this->assertStringNotContainsString('expired', strtolower($error),
            'Error must not reveal coupon expiry status');
        $this->assertStringNotContainsString('usage limit', strtolower($error),
            'Error must not reveal usage limit status');
    }

    public function test_coupon_error_does_not_reveal_usage_limit_hit(): void
    {
        $this->createCoupon([
            'code'      => 'USEDOUT',
            'is_active' => true,
            'max_uses'  => 1,
            'used_count' => 1,
        ]);

        $component = Livewire::test(\App\Livewire\CartPage::class)
            ->set('couponInput', 'USEDOUT')
            ->call('applyCoupon');

        $error = $component->get('couponError');

        $this->assertStringNotContainsString('usage limit', strtolower($error));
        $this->assertStringNotContainsString('reached', strtolower($error));
    }
}
