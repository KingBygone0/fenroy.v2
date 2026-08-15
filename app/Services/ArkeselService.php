<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArkeselService
{
    public function send(string $phone, string $message): bool
    {
        $normalized = $this->normalizePhone($phone);

        if (! $normalized) {
            Log::warning('ArkeselService: invalid phone', ['phone' => $phone]);
            return false;
        }

        try {
            $response = Http::withHeaders([
                'api-key' => config('arkesel.api_key'),
            ])->post(config('arkesel.url'), [
                'sender'     => config('arkesel.sender_id'),
                'message'    => $message,
                'recipients' => [$normalized],
            ]);

            if (! $response->successful()) {
                Log::error('ArkeselService: send failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('ArkeselService: exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function orderReceived(Order $order): bool
    {
        $message = "{$order->customer_name}: Order #{$order->order_number} received. We will confirm your payment shortly.";
        return $this->send($order->customer_phone, $message);
    }

    public function paymentConfirmed(Order $order): bool
    {
        $message = "{$order->customer_name}: Payment confirmed for order #{$order->order_number}. Your items are being picked now.";
        return $this->send($order->customer_phone, $message);
    }

    public function orderPicking(Order $order): bool
    {
        $message = "{$order->customer_name}: Your order #{$order->order_number} is being picked from our shelves. We'll update you when it's packed.";
        return $this->send($order->customer_phone, $message);
    }

    public function orderPacked(Order $order): bool
    {
        $message = "{$order->customer_name}: Order #{$order->order_number} is packed and ready for dispatch. A rider will pick it up shortly.";
        return $this->send($order->customer_phone, $message);
    }

    public function outForDelivery(Order $order, string $riderName = 'our rider', string $riderPhone = ''): bool
    {
        $riderDetail = $riderPhone ? "{$riderName}, {$riderPhone}" : $riderName;
        $message     = "{$order->customer_name} order #{$order->order_number} is on its way! Rider: {$riderDetail}.";
        return $this->send($order->customer_phone, $message);
    }

    public function orderDelivered(Order $order): bool
    {
        $message = "{$order->customer_name}: Order #{$order->order_number} delivered! Thank you for shopping with us. Rate your experience in the app.";
        return $this->send($order->customer_phone, $message);
    }

    public function refundIssued(Order $order): bool
    {
        $total   = number_format($order->total, 2);
        $message = "{$order->customer_name}: A refund of GHS {$total} for order #{$order->order_number} has been issued to your payment method.";
        return $this->send($order->customer_phone, $message);
    }

    public function notifyAdmin(Order $order): bool
    {
        $adminPhone = config('arkesel.admin_phone');
        if (! $adminPhone) {
            return false;
        }
        $total   = number_format($order->total, 2);
        $message = "Fenroy: New order {$order->order_number} from {$order->customer_name} ({$order->customer_phone}). Total: GHS {$total}.";
        return $this->send($adminPhone, $message);
    }

    private function normalizePhone(string $phone): ?string
    {
        // Remove all spaces, dashes, parentheses
        $clean = preg_replace('/[\s\-\(\)]+/', '', $phone);

        if (! $clean) {
            return null;
        }

        // Already E.164 with +233
        if (str_starts_with($clean, '+233') && strlen($clean) === 13) {
            return $clean;
        }

        // 233xxxxxxxxx (12 digits, no +)
        if (str_starts_with($clean, '233') && strlen($clean) === 12) {
            return '+' . $clean;
        }

        // 0xxxxxxxxx (10 digits, Ghana local)
        if (str_starts_with($clean, '0') && strlen($clean) === 10) {
            return '+233' . substr($clean, 1);
        }

        // 9 digits with no prefix — assume Ghana
        if (strlen($clean) === 9 && ctype_digit($clean)) {
            return '+233' . $clean;
        }

        return null;
    }
}
