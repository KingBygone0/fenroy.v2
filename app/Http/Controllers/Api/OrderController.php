<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\PendingOrder;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        private CheckoutService $checkout,
        private OrderService $orderService,
        private CartService $cartService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json(OrderCollection::make($orders));
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json(new OrderResource($order));
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $cart->load('items.product');
        $rawItems = $this->cartService->toArray($cart);

        $items = $this->checkout->validateAndPriceItems($rawItems);
        if (empty($items)) {
            return response()->json(['message' => 'Your cart contains no available products.'], 422);
        }

        try {
            $totals = $this->checkout->computeTotals($items, $request->zone_id, $cart->coupon_code);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $ref = 'FEN-' . strtoupper(Str::random(20));
        $secretKey = Setting::get('paystack_secret_key') ?: config('paystack.secret_key');

        $deliveryWindowLabel = match ($request->delivery_window) {
            'morning'   => 'Today, 8am – 12pm',
            'afternoon' => 'Today, 12pm – 4pm',
            'evening'   => 'Today, 4pm – 8pm',
            default     => $request->delivery_window,
        };

        $paystackResponse = Http::withToken($secretKey)
            ->post(config('paystack.payment_url') . '/transaction/initialize', [
                'email'     => $request->email,
                'amount'    => (int) round($totals['total'] * 100),
                'currency'  => config('paystack.currency', 'GHS'),
                'reference' => $ref,
                'metadata'  => ['order_ref' => $ref],
            ]);

        if (!$paystackResponse->successful()) {
            return response()->json(['message' => 'Payment initialization failed. Please try again.'], 502);
        }

        PendingOrder::create([
            'paystack_ref' => $ref,
            'user_id'      => $request->user()->id,
            'payload'      => [
                'user_id'         => $request->user()->id,
                'name'            => $request->name,
                'email'           => $request->email,
                'phone'           => $request->phone,
                'address'         => $request->address,
                'delivery_zone'   => $totals['zone']->name,
                'delivery_window' => $deliveryWindowLabel,
                'items'           => $items,
                'subtotal'        => $totals['subtotal'],
                'delivery_fee'    => $totals['delivery_fee'],
                'discount'        => $totals['discount'],
                'coupon_code'     => $totals['coupon_code'],
                'total'           => $totals['total'],
                'notes'           => $request->notes,
            ],
            'expires_at' => now()->addHours(2),
        ]);

        return response()->json([
            'paystack_ref'      => $ref,
            'authorization_url' => $paystackResponse->json('data.authorization_url'),
            'amount'            => (int) round($totals['total'] * 100),
            'total_ghs'         => $totals['total'],
        ]);
    }

    public function paystackCallback(Request $request): JsonResponse
    {
        $request->validate([
            'reference' => ['required', 'regex:/^[A-Za-z0-9\-_]+$/'],
        ]);

        $ref = $request->input('reference');

        $pending = PendingOrder::where('paystack_ref', $ref)
            ->where('user_id', $request->user()->id)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();

        if (!$pending) {
            return response()->json(['message' => 'Order session expired or not found.'], 400);
        }

        $secretKey = Setting::get('paystack_secret_key') ?: config('paystack.secret_key');

        $response = Http::withToken($secretKey)
            ->get(config('paystack.payment_url') . '/transaction/verify/' . $ref);

        if (!$response->successful()) {
            return response()->json(['message' => 'Payment verification failed.'], 502);
        }

        $data = $response->json('data');
        if (($data['status'] ?? '') !== 'success') {
            return response()->json(['message' => 'Payment was not successful.'], 400);
        }

        $orderData    = $pending->payload;
        $paidKobo     = $data['amount'];
        $expectedKobo = round($orderData['total'] * 100);

        if ($paidKobo < $expectedKobo) {
            return response()->json(['message' => 'Amount mismatch.'], 400);
        }

        $result = $this->orderService->createFromPaystackPayment($orderData, $ref);
        $order  = $result['order'];

        if (!$result['already_existed']) {
            $this->orderService->dispatchNotifications($order, $orderData['items'] ?? []);
        }

        $cart = $this->cartService->getOrCreateCart($request->user());
        $this->cartService->clearCart($cart);

        $pending->delete();

        return response()->json([
            'status'       => 'success',
            'order_number' => $order->order_number,
        ]);
    }
}
