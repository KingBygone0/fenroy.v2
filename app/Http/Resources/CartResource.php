<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_count'  => $this->items->count(),
            'subtotal'    => (float) $this->subtotal,
            'discount'    => (float) $this->discount,
            'coupon_code' => $this->coupon_code,
            'items'       => CartItemResource::collection($this->items),
        ];
    }
}
