<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Confirmed — Fenroy</title>
    <style>
        body { margin: 0; padding: 0; background: #F8F8F8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #111; }
        .wrap { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .header { background: #C8102E; padding: 28px 32px; text-align: center; }
        .header h1 { color: #fff; font-size: 22px; margin: 0; letter-spacing: -0.3px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin: 4px 0 0; }
        .body { padding: 32px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .order-box { background: #F8F8F8; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .order-box .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .order-box .row.total { border-top: 1px solid #E8E8E8; margin-top: 8px; padding-top: 12px; font-size: 16px; font-weight: 700; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .items-table th { text-align: left; font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.05em; padding: 0 0 8px; border-bottom: 1px solid #E8E8E8; }
        .items-table td { padding: 10px 0; font-size: 14px; border-bottom: 1px solid #F2F2F2; vertical-align: top; }
        .items-table .price { text-align: right; font-weight: 600; white-space: nowrap; }
        .btn { display: block; text-align: center; color: #fff; text-decoration: none; font-weight: 600; font-size: 15px; padding: 14px 24px; border-radius: 999px; margin-bottom: 12px; }
        .btn-red    { background: #C8102E; }
        .btn-dark   { background: #222; }
        .footer { padding: 20px 32px; border-top: 1px solid #F2F2F2; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <img src="https://fenroy.shop/images/fenroy-logo-white.png" alt="Fenroy" style="height:44px;width:auto;margin-bottom:12px;display:block;margin-left:auto;margin-right:auto;">
        <h1>Order Confirmed!</h1>
        <p>Order #{{ $order->order_number }}</p>
    </div>
    <div class="body">
        <p class="greeting">Hi {{ $order->customer_name }}, your order has been placed and is being prepared.</p>

        {{-- Items --}}
        <table class="items-table">
            <tr>
                <th>Item</th>
                <th style="text-align:right">Subtotal</th>
            </tr>
            @foreach(is_array($order->items) ? $order->items : (json_decode($order->items, true) ?? []) as $item)
            <tr>
                <td>
                    <strong>{{ $item['name'] }}</strong><br>
                    <span style="color:#888;font-size:12px">{{ $item['unit'] ?? '' }} × {{ $item['qty'] }}</span>
                </td>
                <td class="price">GH₵ {{ number_format($item['price'] * $item['qty'], 2) }}</td>
            </tr>
            @endforeach
        </table>

        {{-- Totals --}}
        <div class="order-box">
            <div class="row">
                <span>Subtotal</span>
                <span>GH₵ {{ number_format($order->total - $order->delivery_fee + $order->discount, 2) }}</span>
            </div>
            @if($order->delivery_fee > 0)
            <div class="row">
                <span>Delivery</span>
                <span>GH₵ {{ number_format($order->delivery_fee, 2) }}</span>
            </div>
            @else
            <div class="row">
                <span>Delivery</span>
                <span style="color:#16a34a">Free</span>
            </div>
            @endif
            @if($order->discount > 0)
            <div class="row" style="color:#16a34a">
                <span>Discount</span>
                <span>−GH₵ {{ number_format($order->discount, 2) }}</span>
            </div>
            @endif
            <div class="row total">
                <span>Total paid</span>
                <span>GH₵ {{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        {{-- Delivery info --}}
        <div style="font-size:14px;margin-bottom:24px;padding:16px 20px;border:1px solid #E8E8E8;border-radius:12px;">
            <div style="font-weight:600;margin-bottom:8px;">Delivery details</div>
            <div>{{ $order->delivery_address }}</div>
            @if($order->delivery_window)
            <div style="color:#888;margin-top:4px;">{{ $order->delivery_window }}</div>
            @endif
        </div>

        <a href="{{ route('order.track', $order->order_number) }}" class="btn btn-red">Track your order</a>
        <a href="{{ route('order.receipt', $order->order_number) }}" class="btn btn-dark">Download Receipt</a>

        <p style="font-size:14px;color:#666;margin:12px 0 0;">
            Questions? Reply to this email or WhatsApp us. We're happy to help.
        </p>
    </div>
    <div class="footer">
        Fenroy Supermarket &mdash; Delivered fresh to your door.<br>
        &copy; {{ date('Y') }} Fenroy. All rights reserved.
    </div>
</div>
</body>
</html>
