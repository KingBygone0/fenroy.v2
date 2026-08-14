<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\ArkeselService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $sms = new ArkeselService();

        match ($order->status) {
            'processing'       => $sms->orderReceived($order),
            'picking'          => $sms->orderPicking($order),
            'packed'           => $sms->orderPacked($order),
            'out-for-delivery' => $sms->outForDelivery($order),
            'delivered'        => $sms->orderDelivered($order),
            'refunded'         => $sms->refundIssued($order),
            default            => null,
        };
    }
}
