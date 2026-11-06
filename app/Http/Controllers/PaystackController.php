<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\PendingOrder;
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

        // Prefer the server-side pending order; fall back to the session copy.
        // A session copy that names a different reference is rejected, so a payment
        // for one checkout can't be used to confirm another.
        $orderData = PendingOrder::where('paystack_ref', $ref)->first()?->payload;
        if (! $orderData) {
            $sessionOrder = session('pending_order');
            if ($sessionOrder && (! isset($sessionOrder['paystack_ref']) || $sessionOrder['paystack_ref'] === $ref)) {
                $orderData = $sessionOrder;
            }
        }
        if (! $orderData) {
            return response()->json(['status' => 'error', 'message' => 'Order session expired.'], 400);
        }

        if (! $this->paymentCovers($data, $orderData, $ref)) {
            return response()->json(['status' => 'error', 'message' => 'Amount mismatch.'], 400);
        }

        $result = $this->orderService->createFromPaystackPayment($orderData, $ref);
        $order  = $result['order'];
        PendingOrder::where('paystack_ref', $ref)->delete();

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
        $signature = (string) $request->header('X-Paystack-Signature');
        $body      = $request->getContent();

        // Paystack signs webhooks with the account's SECRET KEY. A separately configured
        // webhook secret is still accepted for backwards compatibility.
        $keys = array_values(array_filter([
            Setting::get('paystack_secret_key') ?: config('paystack.secret_key'),
            Setting::get('paystack_webhook_secret') ?: config('paystack.webhook_secret'),
        ]));

        if (empty($keys)) {
            Log::error('Paystack webhook: no secret key configured — rejecting all webhook calls');
            return response('Bad Request', 400);
        }

        $valid = $signature !== '' && collect($keys)->contains(
            fn ($key) => hash_equals(hash_hmac('sha512', $body, $key), $signature)
        );

        if (! $valid) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);
            return response('Bad Request', 400);
        }

        $event = $request->json('event');

        if ($event === 'charge.success') {
            $ref   = $request->json('data.reference');
            $order = Order::where('paystack_ref', $ref)->first();

            if (! $order) {
                // verify() never ran (customer closed the app or lost signal after paying).
                // Rebuild the order from the server-side pending order.
                return $this->createFromWebhook($ref, (array) $request->json('data'));
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

    private function createFromWebhook(?string $ref, array $data): \Illuminate\Http\Response
    {
        $pending = $ref ? PendingOrder::where('paystack_ref', $ref)->first() : null;

        if (! $pending) {
            Log::warning('Paystack webhook: charge.success for unknown reference', ['ref' => $ref]);
            return response('OK', 200);
        }

        $orderData = $pending->payload;

        if (! $this->paymentCovers($data, $orderData, $ref)) {
            return response('OK', 200);
        }

        $result = $this->orderService->createFromPaystackPayment($orderData, $ref);
        $pending->delete();

        if (! $result['already_existed']) {
            $this->orderService->dispatchNotifications($result['order'], $orderData['items'] ?? []);
            Log::info('Paystack webhook: created order from pending order', [
                'order_number' => $result['order']->order_number,
                'ref'          => $ref,
            ]);
        }

        return response('OK', 200);
    }

    /** Paid amount (in pesewas) and currency must cover the order total. */
    private function paymentCovers(array $data, array $orderData, ?string $ref): bool
    {
        $paid     = (int) ($data['amount'] ?? 0);
        $expected = (int) round(($orderData['total'] ?? 0) * 100);
        $currency = strtoupper($data['currency'] ?? 'GHS');

        if ($paid < $expected || $currency !== 'GHS') {
            Log::warning('Paystack: amount/currency mismatch', [
                'paid'     => $paid,
                'expected' => $expected,
                'currency' => $currency,
                'ref'      => $ref,
            ]);
            return false;
        }

        return true;
    }
}
