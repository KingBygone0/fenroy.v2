<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'order_number'     => $this->order_number,
            'status'           => $this->status,
            'payment_status'   => $this->payment_status,
            'total'            => (float) $this->total,
            'delivery_fee'     => (float) $this->delivery_fee,
            'discount'         => (float) $this->discount,
            'coupon_code'      => $this->coupon_code,
            'customer_name'    => $this->customer_name,
            'customer_email'   => $this->customer_email,
            'customer_phone'   => $this->customer_phone,
            'delivery_address' => $this->delivery_address,
            'delivery_window'  => $this->delivery_window,
            'items'            => $this->items,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}
