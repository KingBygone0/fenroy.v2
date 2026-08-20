<?php

namespace App\Services;

use App\Mail\OrderConfirmedMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private ArkeselService $sms,
        private StockAlertService $stockAlert,
    ) {}

    public function createFromPaystackPayment(array $orderData, string $ref): array
    {
        $order          = null;
        $alreadyExisted = false;

        try {
            DB::transaction(function () use ($ref, $orderData, &$order, &$alreadyExisted) {
                $existing = Order::where('paystack_ref', $ref)->lockForUpdate()->first();

                if ($existing) {
                    $order          = $existing;
                    $alreadyExisted = true;
                    return;
                }

                $couponCode = $orderData['coupon_code'] ?? null;
                $discount   = $orderData['discount']    ?? 0;

                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $coupon || ! $coupon->isValid($orderData['subtotal'] ?? 0)) {
                        $couponCode = null;
                        $discount   = 0;
                        Log::info('OrderService: coupon invalidated at payment time', [
                            'code' => $orderData['coupon_code'],
                            'ref'  => $ref,
                        ]);
                    }
                }

                $order = Order::create([
                    'user_id'          => $orderData['user_id'] ?? null,
                    'order_number'     => 'FEN-' . strtoupper(Str::random(12)),
                    'customer_name'    => $orderData['name'],
                    'customer_email'   => $orderData['email'],
                    'customer_phone'   => $orderData['phone'],
                    'delivery_address' => $orderData['address'] ?? '',
                    'delivery_window'  => $orderData['delivery_window'] ?? '',
                    'total'            => $orderData['total'],
                    'delivery_fee'     => $orderData['delivery_fee'] ?? 0,
                    'discount'         => $discount,
                    'coupon_code'      => $couponCode,
                    'items'            => json_encode($orderData['items'] ?? []),
                    'status'           => 'processing',
                    'payment_status'   => 'paid',
                    'paystack_ref'     => $ref,
                    'notes'            => 'Paid via Paystack. Ref: ' . $ref,
                ]);

                if ($couponCode) {
                    Coupon::where('code', $couponCode)->increment('used_count');
                }

                $items     = $orderData['items'] ?? [];
                $qtyBySlug = [];
                foreach ($items as $item) {
                    $slug = $item['slug'] ?? null;
                    if ($slug) {
                        $qtyBySlug[$slug] = ($qtyBySlug[$slug] ?? 0) + (int) ($item['qty'] ?? 1);
                    }
                }

                if (! empty($qtyBySlug)) {
                    $products = Product::whereIn('slug', array_keys($qtyBySlug))
                        ->whereNotNull('stock')
                        ->lockForUpdate()
                        ->get();

                    foreach ($products as $product) {
                        $qty      = $qtyBySlug[$product->slug] ?? 0;
                        $newStock = max(0, $product->stock - $qty);
                        $product->update(['stock' => $newStock]);
                    }
                }
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                $order          = Order::where('paystack_ref', $ref)->firstOrFail();
                $alreadyExisted = true;
            } else {
                throw $e;
            }
        }

        return ['order' => $order, 'already_existed' => $alreadyExisted];
    }

    public function dispatchNotifications(Order $order, array $items = []): void
    {
        try {
            $this->sms->orderReceived($order);
            $this->sms->paymentConfirmed($order);
            $this->sms->notifyAdmin($order);
        } catch (\Throwable $e) {
            Log::warning('SMS failed for order ' . $order->order_number . ': ' . $e->getMessage());
        }

        try {
            $this->stockAlert->checkAfterOrder($items);
        } catch (\Throwable $e) {
            Log::warning('Stock alert failed: ' . $e->getMessage());
        }

        try {
            Mail::to($order->customer_email)->send(new OrderConfirmedMail($order));
        } catch (\Throwable $e) {
            Log::warning('Receipt email failed for order ' . $order->order_number . ': ' . $e->getMessage());
        }

        $order->update(['notified_at' => now()]);
    }
}
