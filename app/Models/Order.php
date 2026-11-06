<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'customer_name', 'customer_email', 'customer_phone',
        'delivery_address', 'delivery_window', 'substitution', 'total', 'delivery_fee',
        'discount', 'coupon_code', 'status', 'payment_status', 'payment_method',
        'paystack_ref', 'items', 'notes', 'is_walk_in', 'notified_at',
    ];

    protected $casts = [
        'total'        => 'float',
        'delivery_fee' => 'float',
        'discount'     => 'float',
        'is_walk_in'   => 'boolean',
        'notified_at'  => 'datetime',
    ];

    /**
     * Always an array. Older online orders were saved double-encoded
     * (a JSON string inside JSON), so decode until we get an array.
     */
    protected function items(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $decoded = $value;
                for ($i = 0; $i < 2 && is_string($decoded); $i++) {
                    $decoded = json_decode($decoded, true);
                }
                return is_array($decoded) ? $decoded : [];
            },
            set: function ($value) {
                if (is_string($value)) {
                    $value = json_decode($value, true) ?? [];
                }
                return json_encode($value ?? []);
            },
        );
    }
}
