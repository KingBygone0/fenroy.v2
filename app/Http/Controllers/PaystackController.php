<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaystackController extends Controller
{
    public function __construct(private OrderService $orderService) {}

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

        $result = $this->orderService->createFromPaystackPayment($orderData, $ref);
        $order  = $result['order'];

        if ($result['already_existed']) {
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

        $this->orderService->dispatchNotifications($order, $orderData['items'] ?? []);

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
                    $sms = new \App\Services\ArkeselService();
                    $sms->paymentConfirmed($order);
                    $sms->notifyAdmin($order);
                } catch (\Throwable $e) {
                    Log::warning('Webhook SMS failed for order ' . $order->order_number . ': ' . $e->getMessage());
                }

                try {
                    (new \App\Services\StockAlertService())->checkAfterOrder($order->items ?? []);
                } catch (\Throwable $e) {
                    Log::warning('Webhook stock alert failed for order ' . $order->order_number . ': ' . $e->getMessage());
                }

                try {
                    Mail::to($order->customer_email)->send(new \App\Mail\OrderConfirmedMail($order));
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
