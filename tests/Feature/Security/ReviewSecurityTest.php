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

    public function test_unauthenticated_user_cannot_submit_review(): void
    {
        $product = Product::factory()->create(['slug' => 'test-item', 'is_active' => true]);

        Livewire::test(\App\Livewire\ProductReviewForm::class, ['productSlug' => 'test-item'])
            ->set('reviewer_name', 'Hacker')
            ->set('body', 'This is a fake review injected without auth.')
            ->call('submit');

        $this->assertDatabaseMissing('reviews', ['product_id' => $product->id]);
    }

    public function test_user_cannot_submit_duplicate_review(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'some-product', 'is_active' => true]);

        Review::factory()->create([
            'product_id' => $product->id,
            'user_id'    => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\ProductReviewForm::class, ['productSlug' => 'some-product'])
            ->set('reviewer_name', $user->name)
            ->set('body', 'Trying to post a second review.')
            ->call('submit');

        $this->assertCount(1, Review::where('product_id', $product->id)->get());
    }

    public function test_reviewer_name_is_sanitized(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'safe-product', 'is_active' => true]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\ProductReviewForm::class, ['productSlug' => 'safe-product'])
            ->set('reviewer_name', '<script>alert(1)</script>John')
            ->set('body', 'Great product!')
            ->call('submit');

        $review = Review::where('product_id', $product->id)->first();
        $this->assertNotNull($review);
        $this->assertStringNotContainsString('<script>', $review->reviewer_name);
    }
}
