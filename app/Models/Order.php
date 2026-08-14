<?php

namespace App\Models;

use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'customer_email', 'customer_phone',
        'delivery_address', 'delivery_window', 'total', 'delivery_fee',
        'discount', 'coupon_code', 'status', 'payment_status',
        'paystack_ref', 'items', 'notes',
    ];

    protected $casts = [
        'total'        => 'float',
        'delivery_fee' => 'float',
        'discount'     => 'float',
        'items'        => 'array',
    ];
}
