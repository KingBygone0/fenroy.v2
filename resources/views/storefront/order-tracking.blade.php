<x-layouts.storefront title="Track Order — Fenroy">
@php
    $num     = $order?->order_number ?? $orderNumber ?? '—';
    $items   = $order ? ($order->items ?? []) : [];
    $total   = $order?->total ?? 0;
    $address = $order?->delivery_address ?? '—';
    $status  = $order?->status ?? 'processing';
    $placed  = $order?->created_at?->format('d M Y, g:ia') ?? '—';
    $isPaid  = $order?->payment_status === 'paid';

    $statusOrder = ['processing', 'picking', 'packed', 'out-for-delivery', 'delivered'];
    $currentIdx  = array_search($status, $statusOrder);
    if ($currentIdx === false) $currentIdx = 0;

    $trackingSteps = [
        ['label' => 'Order Received',    'sub' => $placed],
        ['label' => 'Payment Confirmed', 'sub' => $isPaid ? 'Payment confirmed' : 'Awaiting confirmation'],
        ['label' => 'Picking',           'sub' => 'Items being collected'],
        ['label' => 'Packed',            'sub' => 'Ready for dispatch'],
        ['label' => 'Out for Delivery',  'sub' => 'On the way to you'],
        ['label' => 'Delivered',         'sub' => 'Enjoy your order!'],
    ];
@endphp
<div class="max-w-2xl mx-auto px-4 py-4 md:py-8 pb-24 md:pb-12">

  {{-- ── Top bar ── --}}
  <div class="flex items-center gap-3 mb-5">
    <a href="javascript:history.back()"
       class="w-11 h-11 -ml-2 flex items-center justify-center rounded-full active:bg-[#F5F5F5]" aria-label="Go back">
      <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div>
      <h1 class="text-xl font-extrabold text-brand-text">Order #{{ $num }}</h1>
      <p class="text-sm text-brand-muted">Placed {{ $placed }}</p>
    </div>
  </div>

  {{-- ── Status pill ── --}}
  @php
    $pillMap = [
        'processing'       => ['bg-[#FFF7E6] text-[#B45309]',    'Processing'],
        'picking'          => ['bg-blue-50 text-blue-700',         'Picking items'],
        'packed'           => ['bg-purple-50 text-purple-700',     'Packed'],
        'out-for-delivery' => ['bg-brand-success-tint text-brand-success', 'Out for delivery'],
        'delivered'        => ['bg-brand-success-tint text-brand-success', 'Delivered'],
    ];
    [$pillClass, $pillLabel] = $pillMap[$status] ?? ['bg-[#F5F5F5] text-brand-muted', ucfirst($status)];
  @endphp
  <div class="inline-flex items-center gap-2 h-8 px-4 rounded-full {{ $pillClass }} text-sm font-semibold mb-4">
    <span class="w-2 h-2 rounded-full {{ $status === 'delivered' ? 'bg-brand-success' : 'bg-current opacity-60 animate-pulse' }}"></span>
    {{ $pillLabel }}
  </div>

  {{-- ── Map placeholder ── --}}
  <div class="bg-white rounded-2xl border border-dashed border-brand-border-light h-48 flex items-center justify-center">
    <div class="flex flex-col items-center gap-2 text-brand-muted">
      <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
        <circle cx="12" cy="10" r="3"/>
      </svg>
      <p class="text-sm">Live tracking coming soon</p>
      <p class="text-xs">Your rider's location will appear here.</p>
    </div>
  </div>

  {{-- ── Rider card ── --}}
  <div class="bg-white rounded-2xl border border-brand-border-light p-5 mt-4">
    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted">Your Rider</p>

    @if($status === 'out-for-delivery' || $status === 'delivered')
      <div class="flex items-center gap-3 mt-2">
        <div class="w-12 h-12 rounded-full bg-brand-light-red flex items-center justify-center text-brand-dark-red font-bold text-sm shrink-0">KA</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-brand-text">Kwame Asante</span>
            <span class="text-xs text-brand-warning font-semibold">&#9733; 4.9</span>
          </div>
          <p class="text-xs text-brand-muted">Rider</p>
        </div>
        <span class="inline-flex h-7 px-3 rounded-full bg-brand-success-tint text-brand-success text-xs font-bold items-center whitespace-nowrap">
          {{ $status === 'delivered' ? 'Delivered' : 'Arriving soon' }}
        </span>
      </div>
      <div class="flex gap-2 mt-4">
        <a href="tel:+233244000000"
           class="flex items-center gap-2 h-10 px-5 rounded-full border border-brand-border-light text-sm font-semibold text-brand-text hover:border-brand-text transition-colors">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.62 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          Call Rider
        </a>
        <a href="#"
           class="flex items-center gap-2 h-10 px-5 rounded-full bg-[#E8F5E9] text-[#2E7D32] text-sm font-semibold hover:bg-[#C8E6C9] transition-colors">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          WhatsApp
        </a>
      </div>
    @else
      <div class="flex items-center gap-3 mt-2">
        <div class="w-10 h-10 rounded-full bg-[#E0E0E0] shrink-0 animate-pulse"></div>
        <div>
          <p class="text-sm text-brand-muted italic">Rider being assigned&hellip;</p>
          <p class="text-xs text-brand-muted">You will be notified when a rider is assigned.</p>
        </div>
      </div>
    @endif
  </div>

  {{-- ── Timeline ── --}}
  <div class="bg-white rounded-2xl border border-brand-border-light p-5 mt-4">
    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-4">Order Status</p>
    <div class="relative">
      @foreach($trackingSteps as $i => $step)
        @php
          $done    = $i <= $currentIdx;
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

  {{-- ── Order summary ── --}}
  <div class="bg-white rounded-2xl border border-brand-border-light p-5 mt-4">
    <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Order Summary</p>

    <div class="flex gap-2 text-sm text-brand-secondary-text items-start">
      <svg class="w-4 h-4 text-brand-muted shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <span>{{ $address }}</span>
    </div>

    <div class="mt-3 mb-3 border-t border-brand-border-light"></div>

    @forelse($items as $i => $item)
      @if($i < 3)
        <div class="flex justify-between text-sm py-1">
          <span class="text-brand-secondary-text">{{ $item['qty'] }} &times; {{ $item['name'] }}</span>
          <span class="font-semibold text-brand-text">GH&#8373;&nbsp;{{ number_format($item['price'] * $item['qty'], 2) }}</span>
        </div>
      @endif
    @empty
      <p class="text-sm text-brand-muted">No items found.</p>
    @endforelse

    @if(count($items) > 3)
      <p class="text-sm text-brand-muted mt-2">&hellip;and {{ count($items) - 3 }} more</p>
    @endif

    <div class="flex justify-between font-extrabold text-base pt-3 mt-3 border-t border-brand-border-light">
      <span class="text-brand-text">Total</span>
      <span class="text-brand-text">GH&#8373;&nbsp;{{ number_format($total, 2) }}</span>
    </div>
  </div>

</div>
</x-layouts.storefront>
