<?php

namespace Tests\Feature\Security;

use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_add_uses_db_price_not_client_price(): void
    {
        $product = Product::factory()->create([
            'slug'      => 'test-product',
            'price'     => 99.99,
            'is_active' => true,
        ]);

        $response = $this->postJson('/cart/quick-add', [
            'slug'  => 'test-product',
            'price' => 0.01,
            'name'  => 'Hacked Name',
        ]);

        $response->assertSuccessful();

        $cartItems = session('cart_items', []);
        $item      = collect($cartItems)->firstWhere('slug', 'test-product');

        $this->assertNotNull($item);
        $this->assertEquals(99.99, $item['price'], 'Cart must use DB price, not client-supplied price');
        $this->assertEquals('test-product', $item['slug']);
    }

    public function test_quick_add_rejects_unknown_product(): void
    {
        $response = $this->postJson('/cart/quick-add', [
            'slug' => 'nonexistent-product',
        ]);

        $response->assertStatus(404);
    }

    public function test_coupon_error_does_not_reveal_expiry_vs_usage_limit(): void
    {
        // Create an expired coupon
        $coupon = \App\Models\Coupon::factory()->create([
            'code'       => 'EXPIRED10',
            'is_active'  => true,
            'expires_at' => now()->subDay(),
        ]);

        $component = \Livewire\Livewire::test(\App\Livewire\CartPage::class)
            ->set('couponInput', 'EXPIRED10')
            ->call('applyCoupon');

        $error = $component->get('couponError');
        $this->assertStringNotContainsString('expired', strtolower($error),
            'Error message must not reveal that coupon is expired (coupon enumeration)');
    }
}
