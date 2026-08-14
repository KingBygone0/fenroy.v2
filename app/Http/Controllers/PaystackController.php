<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmedMail;
use App\Models\Order;
use App\Services\ArkeselService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $response = Http::withToken(config('paystack.secret_key'))
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
            return response()->json(['status' => 'error', 'message' => 'Amount mismatch.'], 400);
        }

        $order = Order::create([
            'order_number'    => 'FEN-' . strtoupper(Str::random(6)),
            'customer_name'   => $orderData['name'],
            'customer_email'  => $orderData['email'],
            'customer_phone'  => $orderData['phone'],
            'delivery_address'=> $orderData['address'] ?? '',
            'delivery_window' => $orderData['delivery_window'] ?? '',
            'total'           => $orderData['total'],
            'delivery_fee'    => $orderData['delivery_fee'] ?? 0,
            'discount'        => $orderData['discount'] ?? 0,
            'coupon_code'     => $orderData['coupon_code'] ?? null,
            'items'           => json_encode($orderData['items'] ?? []),
            'status'          => 'processing',
            'payment_status'  => 'paid',
            'paystack_ref'    => $ref,
            'notes'           => 'Paid via Paystack. Ref: ' . $ref,
        ]);

        session()->forget(['pending_order', 'cart_items', 'cart_count', 'cart_total']);

        // SMS notifications
        try {
            $sms = new ArkeselService();
            $sms->orderReceived($order);
            $sms->paymentConfirmed($order);
        } catch (\Throwable $e) {
            Log::warning('SMS failed for order ' . $order->order_number . ': ' . $e->getMessage());
        }

        // Email receipt
        try {
            Mail::to($order->customer_email)->send(new OrderConfirmedMail($order));
        } catch (\Throwable $e) {
            Log::warning('Receipt email failed for order ' . $order->order_number . ': ' . $e->getMessage());
        }

        return response()->json([
            'status'       => 'success',
            'order_number' => $order->order_number,
            'redirect'     => route('order.confirmed', ['order' => $order->order_number]),
        ]);
    }

    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $signature = $request->header('X-Paystack-Signature');
        $body      = $request->getContent();

        if ($signature !== hash_hmac('sha512', $body, config('paystack.webhook_secret'))) {
            return response('Unauthorized', 401);
        }

        $event = $request->json('event');

        if ($event === 'charge.success') {
            $ref = $request->json('data.reference');

            $order = Order::where('paystack_ref', $ref)->first();
            if ($order && $order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                ]);
            }
        }

        return response('OK', 200);
    }
}
