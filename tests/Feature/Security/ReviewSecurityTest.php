<?php

namespace Tests\Feature\Security;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(string $slug = 'test-item'): Product
    {
        return Product::create([
            'name'      => 'Test Product',
            'slug'      => $slug,
            'price'     => 10.00,
            'stock'     => 10,
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_submit_review(): void
    {
        $product = $this->createProduct('unauth-item');

        Livewire::test(\App\Livewire\ProductReviewForm::class, ['productSlug' => 'unauth-item'])
            ->set('reviewer_name', 'Hacker')
            ->set('body', 'Injected review without auth.')
            ->call('submit');

        $this->assertDatabaseMissing('reviews', ['product_id' => $product->id]);
    }

    public function test_user_cannot_submit_duplicate_review(): void
    {
        $user    = User::factory()->create();
        $product = $this->createProduct('dup-product');

        Review::create([
            'product_id'    => $product->id,
            'user_id'       => $user->id,
            'reviewer_name' => $user->name,
            'rating'        => 4,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\ProductReviewForm::class, ['productSlug' => 'dup-product'])
            ->set('reviewer_name', $user->name)
            ->set('body', 'Trying a second review.')
            ->call('submit');

        $this->assertCount(1, Review::where('product_id', $product->id)->get());
    }

    public function test_reviewer_name_is_sanitized_of_html(): void
    {
        $user    = User::factory()->create();
        $product = $this->createProduct('safe-product');

        Livewire::actingAs($user)
            ->test(\App\Livewire\ProductReviewForm::class, ['productSlug' => 'safe-product'])
            ->set('reviewer_name', '<script>alert(1)</script>Kofi')
            ->set('body', 'Great product!')
            ->call('submit');

        $review = Review::where('product_id', $product->id)->first();
        $this->assertNotNull($review);
        $this->assertStringNotContainsString('<script>', $review->reviewer_name);
    }

    public function test_review_body_is_sanitized_of_html(): void
    {
        $user    = User::factory()->create();
        $product = $this->createProduct('body-product');

        Livewire::actingAs($user)
            ->test(\App\Livewire\ProductReviewForm::class, ['productSlug' => 'body-product'])
            ->set('reviewer_name', 'Normal Name')
            ->set('body', '<img src=x onerror=alert(1)>Really good.')
            ->call('submit');

        $review = Review::where('product_id', $product->id)->first();
        $this->assertNotNull($review);
        $this->assertStringNotContainsString('<img', $review->body);
    }
}
