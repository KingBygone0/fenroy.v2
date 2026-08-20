<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;

        return [
            'id'       => $this->id,
            'quantity' => $this->quantity,
            'price'    => $product ? (float) $product->price : (float) $this->price,
            'subtotal' => $product
                ? (float) ($product->price * $this->quantity)
                : (float) ($this->price * $this->quantity),
            'product'  => $product ? [
                'id'        => $product->id,
                'slug'      => $product->slug,
                'name'      => $product->name,
                'unit'      => $product->unit ?? '',
                'image_url' => $product->image_url,
                'stock'     => $product->stock,
            ] : null,
        ];
    }
}
