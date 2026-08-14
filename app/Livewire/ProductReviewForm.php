<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Review;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ProductReviewForm extends Component
{
    public string $productSlug;
    public int $rating = 5;
    public bool $submitted = false;

    #[Rule('required|min:2|max:80')]
    public string $reviewer_name = '';

    #[Rule('nullable|min:10|max:500')]
    public string $body = '';

    public function mount(string $productSlug): void
    {
        $this->productSlug = $productSlug;
        if (auth()->check()) {
            $this->reviewer_name = auth()->user()->name;
        }
    }

    public function setRating(int $r): void
    {
        $this->rating = max(1, min(5, $r));
    }

    public function submit(): void
    {
        $this->validate();

        $product = Product::where('slug', $this->productSlug)->first();
        if (! $product) return;

        Review::create([
            'product_id'    => $product->id,
            'user_id'       => auth()->id(),
            'reviewer_name' => $this->reviewer_name,
            'rating'        => $this->rating,
            'body'          => $this->body ?: null,
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.product-review-form');
    }
}
