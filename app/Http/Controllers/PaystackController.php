<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmedMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ArkeselService;
use App\Services\StockAlertService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaystackController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $ref = $request->input('reference');

        if (! $ref) {
            return response()->json(['status' => 'error', 'message' => 'No reference provided.'], 422);
        }

        // Guard against SSRF via path traversal in reference
        if (! preg_match('/^[A-Za-z0-9\-_]+$/', $ref)) {
            Log::warning('Paystack verify: invalid reference format', ['ref' => $ref, 'ip' => $request->ip()]);
            return response()->json(['status' => 'error', 'message' => 'Invalid reference.'], 422);
        }

        $secretKey = Setting::get('paystack_secret_key') ?: config('paystack.secret_key');

        $response = Http::withToken($secretKey)
            ->get(config('paystack.payment_url') . '/transaction/verify/' . $ref);

        if (! $response->successful()) {
            return response()->json(['status' => 'error', 'message' => 'Verification request failed.'], 502);
        }

        $data = $response->json('data');

        if (($data['status'] ?? '') !== 'success') {
            return response()->json(['status' => 'error', 'message' => 'Payment was not successful.'], 400);
        }

        $orderData = session('pending_order');
        if (! $orderData) {
            return response()->json(['status' => 'error', 'message' => 'Order session expired.'], 400);
        }

        $paidKobo     = $data['amount'];
        $expectedKobo = round($orderData['total'] * 100);

        if ($paidKobo < $expectedKobo) {
            Log::warning('Paystack verify: amount mismatch', [
                'paid'     => $paidKobo,
                'expected' => $expectedKobo,
                'ref'      => $ref,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Amount mismatch.'], 400);
        }

        // Atomic idempotency guard — prevents duplicate orders when verify() is called
        // twice concurrently (e.g. double-click, network retry). The SELECT FOR UPDATE
        // lock combined with the unique index on paystack_ref ensures only one order
        // is ever created per Paystack reference even under parallel requests.
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

                // Re-validate coupon inside the transaction so concurrent verify() calls
                // cannot both honour a single-use coupon (race condition on used_count).
                $couponCode = $orderData['coupon_code'] ?? null;
                $discount   = $orderData['discount']    ?? 0;

                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $coupon || ! $coupon->isValid($orderData['subtotal'] ?? 0)) {
                        // Coupon revoked or maxed out between apply-time and payment-time
                        $couponCode = null;
                        $discount   = 0;
                        Log::info('Paystack verify: coupon invalidated at payment time', [
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

                // Decrement stock for each ordered item — capped at 0 to prevent negative values.
                // Done inside the same transaction so stock changes are rolled back if order fails.
                $items = $orderData['items'] ?? [];
                if (! empty($items)) {
                    $slugs = array_filter(array_column($items, 'slug'));
                    $qtyBySlug = [];
                    foreach ($items as $item) {
                        $slug = $item['slug'] ?? null;
                        if ($slug) {
                            $qtyBySlug[$slug] = ($qtyBySlug[$slug] ?? 0) + (int) ($item['qty'] ?? 1);
                        }
                    }

                    $products = Product::whereIn('slug', array_keys($qtyBySlug))
                        ->whereNotNull('stock')
                        ->lockForUpdate()
                        ->get();

                    foreach ($products as $product) {
                        $qty     = $qtyBySlug[$product->slug] ?? 0;
                        $newStock = max(0, $product->stock - $qty);
                        $product->update(['stock' => $newStock]);
                    }
                }
            });
        } catch (QueryException $e) {
            // Unique constraint violation on paystack_ref — another concurrent request
            // already created the order. Return that order's details.
            if ($e->getCode() === '23000') {
                $order          = Order::where('paystack_ref', $ref)->firstOrFail();
                $alreadyExisted = true;
            } else {
                throw $e;
            }
        }

        if ($alreadyExisted) {
            session()->forget(['pending_order', 'cart_items', 'cart_count', 'cart_total', 'cart_discount', 'cart_coupon']);
            return response()->json([
                'status'       => 'success',
                'order_number' => $order->order_number,
                'redirect'     => route('order.confirmed', ['orderNumber' => $order->order_number]),
            ]);
        }

        session()->forget(['pending_order', 'cart_items', 'cart_count', 'cart_total', 'cart_discount', 'cart_coupon']);

        Log::channel('single')->info('Order created after payment', [
            'order_number' => $order->order_number,
            'ref'          => $ref,
            'total'        => $order->total,
        ]);

        // SMS notifications
        try {
            $sms = new ArkeselService();
            $sms->orderReceived($order);
            $sms->paymentConfirmed($order);
            $sms->notifyAdmin($order);
        } catch (\Throwable $e) {
            Log::warning('SMS failed for order ' . $order->order_number . ': ' . $e->getMessage());
        }

        // Low stock alerts
        try {
            (new StockAlertService())->checkAfterOrder($orderData['items'] ?? []);
        } catch (\Throwable $e) {
            Log::warning('Stock alert failed: ' . $e->getMessage());
        }

        // Email receipt
        try {
            Mail::to($order->customer_email)->send(new OrderConfirmedMail($order));
        } catch (\Throwable $e) {
            Log::warning('Receipt email failed for order ' . $order->order_number . ': ' . $e->getMessage());
        }

        // Mark all side effects as dispatched so the webhook fallback skips them.
        $order->update(['notified_at' => now()]);

        return response()->json([
            'status'       => 'success',
            'order_number' => $order->order_number,
            'redirect'     => route('order.confirmed', ['orderNumber' => $order->order_number]),
        ]);
    }

    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $signature     = $request->header('X-Paystack-Signature');
        $body          = $request->getContent();
        $webhookSecret = Setting::get('paystack_webhook_secret') ?: config('paystack.webhook_secret');

        // Refuse all webhook calls if secret is not configured
        if (empty($webhookSecret)) {
            Log::error('Paystack webhook: webhook_secret not configured — rejecting all webhook calls');
            return response('Bad Request', 400);
        }

        if (! $signature || $signature !== hash_hmac('sha512', $body, $webhookSecret)) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);
            return response('Bad Request', 400);
        }

        $event = $request->json('event');

        if ($event === 'charge.success') {
            $ref   = $request->json('data.reference');
            $order = Order::where('paystack_ref', $ref)->first();

            if (! $order) {
                // verify() never ran (user abandoned before callback) — we cannot recreate
                // the order here because session data is gone. Log for manual review.
                Log::warning('Paystack webhook: charge.success for unknown reference', ['ref' => $ref]);
                return response('OK', 200);
            }

            // If verify()'s transaction never completed, mark the order paid now.
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                ]);

                if ($order->coupon_code) {
                    Coupon::where('code', $order->coupon_code)->increment('used_count');
                }
            }

            // If verify() crashed after the transaction but before dispatching notifications,
            // notified_at will be null. Send them now as a fallback.
            // Stock was already decremented inside verify()'s transaction — do not touch it again.
            if (! $order->notified_at) {
                try {
                    $sms = new ArkeselService();
                    $sms->paymentConfirmed($order);
                    $sms->notifyAdmin($order);
                } catch (\Throwable $e) {
                    Log::warning('Webhook SMS failed for order ' . $order->order_number . ': ' . $e->getMessage());
                }

                try {
                    (new StockAlertService())->checkAfterOrder($order->items ?? []);
                } catch (\Throwable $e) {
                    Log::warning('Webhook stock alert failed for order ' . $order->order_number . ': ' . $e->getMessage());
                }

                try {
                    Mail::to($order->customer_email)->send(new OrderConfirmedMail($order));
                } catch (\Throwable $e) {
                    Log::warning('Webhook email failed for order ' . $order->order_number . ': ' . $e->getMessage());
                }

                $order->update(['notified_at' => now()]);

                Log::info('Paystack webhook: dispatched fallback notifications', [
                    'order_number' => $order->order_number,
                    'ref'          => $ref,
                ]);
            }
        }

        return response('OK', 200);
    }
}
