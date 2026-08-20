<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'name'           => $this->name,
            'sku'            => $this->sku,
            'description'    => $this->description,
            'unit'           => $this->unit ?? '',
            'price'          => (float) $this->price,
            'old_price'      => $this->old_price ? (float) $this->old_price : null,
            'stock'          => $this->stock,
            'category'       => $this->category,
            'type'           => $this->type,
            'image_url'      => $this->image_url,
            'is_featured'    => (bool) $this->is_featured,
            'is_best_seller' => (bool) $this->is_best_seller,
            'reviews'        => $this->whenLoaded('reviews', fn() =>
                $this->reviews->map(fn($r) => [
                    'reviewer_name' => $r->reviewer_name,
                    'rating'        => $r->rating,
                    'body'          => $r->body,
                    'created_at'    => $r->created_at?->toDateString(),
                ])
            ),
        ];
    }
}
