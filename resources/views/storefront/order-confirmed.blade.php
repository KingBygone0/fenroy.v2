<x-layouts.storefront title="Order Confirmed — Fenroy">
@php
    $num      = $order?->order_number ?? $orderNumber ?? 'FEN-XXXXXX';
    $hasOrder = $order !== null;
    $items    = $hasOrder ? ($order->items ?? []) : [];
    $total    = $order?->total ?? 0;
    $address  = $order?->delivery_address ?? '—';
    $window   = $order?->delivery_window   ?? '—';
    $isPaid   = $order?->payment_status === 'paid';
    $placed   = $order?->created_at?->format('d M Y, g:ia') ?? now()->format('d M Y, g:ia');
@endphp
<div class="max-w-2xl mx-auto px-4 py-8 pb-24 md:pb-12">

  {{-- ── Success Banner ── --}}
  <div class="text-center mb-6">
    <div class="w-16 h-16 rounded-full bg-brand-success-tint flex items-center justify-center mx-auto">
      <svg class="w-8 h-8 text-brand-success" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <h1 class="text-2xl md:text-3xl font-extrabold text-brand-text mt-4">Order Confirmed!</h1>
    <p class="text-sm text-brand-muted mt-1">#{{ $num }}</p>

    <div class="inline-flex items-center gap-2 mt-3 h-8 px-4 rounded-full text-sm font-semibold
        {{ $isPaid ? 'bg-brand-success-tint text-brand-success' : 'bg-[#FFF7E6] text-[#B45309]' }}">
      <span class="w-2 h-2 rounded-full {{ $isPaid ? 'bg-brand-success' : 'bg-brand-warning animate-pulse' }}"></span>
      {{ $isPaid ? 'Payment confirmed' : 'Awaiting payment confirmation' }}
    </div>
  </div>

  @if($hasOrder)
  {{-- ── Delivery card ── --}}
  <div class="bg-white rounded-2xl border border-brand-border-light p-5 mt-5">
    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Delivery</p>

    <div class="flex items-start gap-3">
      <svg class="w-4 h-4 text-brand-muted shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
        <circle cx="12" cy="10" r="3"/>
      </svg>
      <p class="text-sm text-brand-text font-medium">{{ $address }}</p>
    </div>

    <div class="mt-3 mb-3 border-t border-brand-border-light"></div>

    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-semibold text-brand-text">{{ $window ?: '—' }}</p>
        <p class="text-xs text-brand-muted">Delivery window</p>
      </div>
      <span class="inline-flex h-6 px-2.5 rounded-full bg-brand-success-tint text-brand-success text-[11px] font-bold items-center">On track</span>
    </div>

    <div class="mt-3 mb-3 border-t border-brand-border-light"></div>

    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-[#E0E0E0] shrink-0 animate-pulse"></div>
      <div>
        <p class="text-sm text-brand-muted italic">Rider being assigned&hellip;</p>
        <p class="text-xs text-brand-muted">You will be notified when a rider is assigned.</p>
      </div>
    </div>
  </div>

  {{-- ── Order items card ── --}}
  <div class="bg-white rounded-2xl border border-brand-border-light p-5 mt-4" x-data="{ expanded: false }">
    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Your Order</p>

    @forelse($items as $i => $item)
      @if($i < 3)
        <div class="flex justify-between text-sm py-1">
          <span class="text-brand-secondary-text">{{ $item['qty'] }} &times; {{ $item['name'] }}</span>
          <span class="font-semibold text-brand-text">GH&#8373;&nbsp;{{ number_format($item['price'] * $item['qty'], 2) }}</span>
        </div>
      @else
        <div x-show="expanded" x-cloak class="flex justify-between text-sm py-1">
          <span class="text-brand-secondary-text">{{ $item['qty'] }} &times; {{ $item['name'] }}</span>
          <span class="font-semibold text-brand-text">GH&#8373;&nbsp;{{ number_format($item['price'] * $item['qty'], 2) }}</span>
        </div>
      @endif
    @empty
      <p class="text-sm text-brand-muted">No items found.</p>
    @endforelse

    @if(count($items) > 3)
      <p x-show="!expanded" @click="expanded = true"
         class="text-sm text-brand-red cursor-pointer hover:underline mt-2 select-none">
        &hellip;and {{ count($items) - 3 }} more {{ count($items) - 3 === 1 ? 'item' : 'items' }}
      </p>
    @endif

    <div class="flex justify-between font-extrabold text-base mt-3 pt-3 border-t border-brand-border-light">
      <span class="text-brand-text">Total</span>
      <span class="text-brand-text">GH&#8373;&nbsp;{{ number_format($total, 2) }}</span>
    </div>
  </div>

  {{-- ── Status timeline ── --}}
  <div class="bg-white rounded-2xl border border-brand-border-light p-5 mt-4">
    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-4">Order Status</p>

    @php
      $status = $order?->status ?? 'processing';
      $statusOrder = ['processing', 'picking', 'packed', 'out-for-delivery', 'delivered'];
      $currentIdx  = array_search($status, $statusOrder) ?: 0;

      $steps = [
        ['label' => 'Order Received',    'sub' => $placed],
        ['label' => 'Payment Confirmed', 'sub' => $isPaid ? 'Confirmed' : 'Awaiting confirmation'],
        ['label' => 'Picking',           'sub' => 'Items being collected'],
        ['label' => 'Packed',            'sub' => 'Ready for dispatch'],
        ['label' => 'Out for Delivery',  'sub' => 'On the way to you'],
        ['label' => 'Delivered',         'sub' => 'Enjoy your order!'],
      ];
    @endphp

    <div class="relative">
      @foreach($steps as $i => $step)
        @php
          $done    = $i < $currentIdx + 1;
          $current = $i === $currentIdx + 1;
          $isLast  = $loop->last;
        @endphp
        <div class="flex items-start gap-3 {{ !$isLast ? 'mb-4' : '' }}">
          <div class="w-6 shrink-0 flex flex-col items-center">
            @if($done)
              <div class="w-6 h-6 rounded-full bg-brand-success flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            @elseif($current)
              <div class="w-6 h-6 rounded-full bg-brand-warning animate-pulse flex items-center justify-center">
                <span class="w-2.5 h-2.5 rounded-full bg-white"></span>
              </div>
            @else
              <div class="w-6 h-6 rounded-full border-2 border-brand-border-light bg-white"></div>
            @endif
            @if(!$isLast)
              <div class="w-0.5 h-4 mx-auto mt-1 {{ $done ? 'bg-brand-success' : 'bg-brand-border-light' }}"></div>
            @endif
          </div>
          <div class="pb-0.5">
            <p class="text-sm font-semibold {{ $done || $current ? 'text-brand-text' : 'text-brand-muted' }}">{{ $step['label'] }}</p>
            <p class="text-xs text-brand-muted mt-0.5">{{ $step['sub'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- ── Actions ── --}}
  <div class="flex flex-col md:flex-row gap-3 mt-6">
    <a href="{{ route('order.track', $num) }}"
       class="h-12 px-8 rounded-full bg-brand-text hover:bg-black text-white text-sm font-semibold inline-flex items-center justify-center cursor-pointer transition-colors">
      Track Order
    </a>
    <a href="{{ route('home') }}"
       class="h-12 px-8 rounded-full border-2 border-brand-border-light hover:border-brand-text text-brand-text text-sm font-semibold inline-flex items-center justify-center cursor-pointer transition-colors">
      Continue Shopping
    </a>
  </div>
  @else
  {{-- Generic confirmation — shown when order details cannot be verified for this viewer --}}
  <div class="mt-6 text-center">
    <p class="text-sm text-brand-secondary-text">Your order has been received. Check your email for a confirmation receipt.</p>
    <a href="{{ route('home') }}" class="mt-5 h-12 px-8 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold inline-flex items-center justify-center cursor-pointer transition-colors">
      Continue Shopping
    </a>
  </div>
  @endif

</div>
</x-layouts.storefront>
