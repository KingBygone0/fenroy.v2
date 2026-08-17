<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Receipt {{ $order->order_number }} — Fenroy</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    background: #f0f0f0;
    font-family: 'Courier New', Courier, monospace;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 16px 40px;
  }

  .print-btn {
    margin-bottom: 20px;
    padding: 10px 28px;
    background: #C8102E;
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }
  .print-btn:hover { background: #a80000; }

  .receipt {
    background: #fff;
    width: 100%;
    max-width: 380px;
    padding: 28px 24px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
  }

  .logo-wrap {
    text-align: center;
    margin-bottom: 6px;
  }
  .logo-wrap img {
    height: 40px;
    width: auto;
  }
  .store-name {
    text-align: center;
    font-size: 20px;
    font-weight: 900;
    letter-spacing: 2px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    margin-bottom: 4px;
  }
  .store-info {
    text-align: center;
    font-size: 12px;
    line-height: 1.7;
  }

  .dash { border: none; border-top: 2px dashed #bbb; margin: 12px 0; }

  .meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    margin-bottom: 4px;
  }
  .meta-row .label { color: #555; }
  .meta-row .val   { font-weight: 700; text-align: right; }

  .col-head {
    display: flex;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding-bottom: 6px;
    border-bottom: 1px solid #ccc;
    margin-bottom: 4px;
  }
  .col-head .c-name { flex: 1; }
  .col-head .c-qty  { width: 36px; text-align: center; }
  .col-head .c-unit { width: 68px; text-align: right; }
  .col-head .c-sub  { width: 72px; text-align: right; }

  .item-row {
    display: flex;
    align-items: baseline;
    font-size: 12px;
    padding: 5px 0;
    border-bottom: 1px dotted #e0e0e0;
  }
  .item-row .c-name { flex: 1; padding-right: 4px; line-height: 1.3; }
  .item-row .c-qty  { width: 36px; text-align: center; }
  .item-row .c-unit { width: 68px; text-align: right; }
  .item-row .c-sub  { width: 72px; text-align: right; font-weight: 700; }

  .totals { font-size: 13px; }
  .total-row {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
  }
  .total-row.grand {
    font-size: 16px;
    font-weight: 900;
    padding: 8px 0 4px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }
  .total-row.paid-via {
    font-size: 12px;
    color: #555;
    padding-top: 2px;
  }

  .order-num {
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 3px;
    margin: 4px 0 8px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  }
  .barcode {
    text-align: center;
    font-size: 9px;
    letter-spacing: 6px;
    color: #333;
    margin: 4px 0 2px;
    font-family: 'Courier New', monospace;
    line-height: 1;
    overflow: hidden;
    white-space: nowrap;
  }
  .bar-lines {
    display: flex;
    justify-content: center;
    gap: 1px;
    height: 36px;
    margin: 6px auto;
    max-width: 220px;
  }
  .bar-lines span {
    display: inline-block;
    height: 100%;
    background: #111;
  }

  .thank-you {
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    margin-bottom: 2px;
  }
  .tagline {
    text-align: center;
    font-size: 11px;
    color: #555;
  }

  @media print {
    body { background: #fff; padding: 0; }
    .print-btn { display: none; }
    .receipt { box-shadow: none; max-width: 100%; padding: 0; }
  }
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">⬇ Print / Save as PDF</button>
<script>
  if (new URLSearchParams(window.location.search).get('print') === '1') {
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
  }
</script>

<div class="receipt">

  {{-- Header --}}
  <div class="logo-wrap">
    <img src="https://fenroy.shop/images/fenroy-logo.png" alt="Fenroy">
  </div>
  <div class="store-name">FENROY</div>
  <div class="store-info">
    Online Supermarket — Ghana<br>
    fenroy.shop &nbsp;|&nbsp; noreply@fenroy.shop
  </div>

  <hr class="dash">

  {{-- Order meta --}}
  <div class="meta-row">
    <span class="label">Order #</span>
    <span class="val">{{ $order->order_number }}</span>
  </div>
  <div class="meta-row">
    <span class="label">Date</span>
    <span class="val">{{ $order->created_at->format('d M Y, g:ia') }}</span>
  </div>
  <div class="meta-row">
    <span class="label">Customer</span>
    <span class="val">{{ $order->customer_name }}</span>
  </div>
  <div class="meta-row">
    <span class="label">Payment</span>
    <span class="val">{{ ucfirst($order->payment_status) }} via Paystack</span>
  </div>
  @if($order->is_walk_in)
  <div class="meta-row">
    <span class="label">Type</span>
    <span class="val">Walk-in Sale</span>
  </div>
  @elseif($order->delivery_address)
  <div class="meta-row">
    <span class="label">Deliver to</span>
    <span class="val" style="max-width:200px;">{{ $order->delivery_address }}</span>
  </div>
  @endif
  @if($order->payment_method)
  <div class="meta-row">
    <span class="label">Paid via</span>
    <span class="val">{{ strtoupper($order->payment_method) }}</span>
  </div>
  @endif

  <hr class="dash">

  {{-- Items --}}
  <div class="col-head">
    <span class="c-name">Item</span>
    <span class="c-qty">Qty</span>
    <span class="c-unit">Unit</span>
    <span class="c-sub">Total</span>
  </div>

  @php $items = is_array($order->items) ? $order->items : (json_decode($order->items, true) ?? []); @endphp
  @foreach($items as $item)
  <div class="item-row">
    <span class="c-name">{{ $item['name'] }}</span>
    <span class="c-qty">{{ $item['qty'] }}</span>
    <span class="c-unit">GH₵ {{ number_format($item['price'], 2) }}</span>
    <span class="c-sub">GH₵ {{ number_format($item['price'] * $item['qty'], 2) }}</span>
  </div>
  @endforeach

  <hr class="dash">

  {{-- Totals --}}
  <div class="totals">
    @php $subtotal = $order->total - $order->delivery_fee + $order->discount; @endphp
    <div class="total-row">
      <span>Subtotal</span>
      <span>GH₵ {{ number_format($subtotal, 2) }}</span>
    </div>
    <div class="total-row">
      <span>Delivery</span>
      <span>{{ $order->delivery_fee > 0 ? 'GH₵ ' . number_format($order->delivery_fee, 2) : 'Free' }}</span>
    </div>
    @if($order->discount > 0)
    <div class="total-row" style="color:#16a34a;">
      <span>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
      <span>−GH₵ {{ number_format($order->discount, 2) }}</span>
    </div>
    @endif
    <div class="total-row grand">
      <span>TOTAL PAID</span>
      <span>GH₵ {{ number_format($order->total, 2) }}</span>
    </div>
  </div>

  <hr class="dash">

  {{-- Barcode-style visual --}}
  <div class="order-num">{{ $order->order_number }}</div>
  <div class="bar-lines" aria-hidden="true">
    @for($i = 0; $i < 55; $i++)
    <span style="width: {{ [1,2,3,1,1,2,1,3,1,2,1,1,2,3,1,2][$i % 16] }}px;
                 opacity: {{ $i % 5 === 0 ? '0.3' : '1' }};"></span>
    @endfor
  </div>

  <hr class="dash">

  <div class="thank-you">THANK YOU!</div>
  <div class="tagline">Delivered fresh to your door &bull; fenroy.shop</div>

</div>

</body>
</html>
