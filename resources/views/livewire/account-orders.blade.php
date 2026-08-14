<div class="space-y-4">

    {{-- Tab bar --}}
    <div class="flex border-b border-brand-border-light mb-1">
        @foreach ([
            'all'        => 'All',
            'processing' => 'Processing',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
        ] as $key => $label)
            <button
                type="button"
                wire:click="switchTab('{{ $key }}')"
                class="mr-5 {{ $tab === $key
                    ? 'text-brand-dark-red font-bold border-b-2 border-brand-red -mb-px pb-3 text-sm'
                    : 'text-brand-secondary-text text-sm pb-3 hover:text-brand-text' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Orders list --}}
    @forelse ($orders as $order)
        <div class="bg-white rounded-2xl border border-brand-border-light p-5">

            {{-- Top row: id + date --}}
            <div class="flex items-center">
                <span class="text-sm font-bold text-brand-text">{{ $order['id'] }}</span>
                <span class="text-sm text-brand-muted ml-auto">{{ $order['date'] }}</span>
            </div>

            {{-- Items summary --}}
            @php
                $itemNames  = array_column($order['items'], 'name');
                $shown      = array_slice($itemNames, 0, 2);
                $extra      = count($itemNames) - 2;
                $summary    = implode(', ', $shown) . ($extra > 0 ? ', and ' . $extra . ' more' : '');
            @endphp
            <p class="text-sm text-brand-secondary-text mt-1">{{ $summary }}</p>

            {{-- Bottom row --}}
            <div class="flex items-center justify-between mt-3 flex-wrap gap-2">

                {{-- Total + status badge --}}
                <div class="flex items-center gap-2">
                    <span class="text-[17px] font-extrabold text-brand-text">GHS {{ number_format($order['total'], 2) }}</span>

                    @php
                        $badgeClass = match ($order['status']) {
                            'Processing' => 'bg-[#FFF7E6] text-[#B45309]',
                            'Delivered'  => 'bg-brand-success-tint text-brand-success',
                            default      => 'bg-[#F5F5F5] text-brand-muted',
                        };
                    @endphp
                    <span class="inline-flex items-center h-6 px-2.5 rounded-full text-[11px] font-bold {{ $badgeClass }}">
                        {{ $order['status'] }}
                    </span>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('order.track', $order['id']) }}"
                       class="h-9 px-4 rounded-full border border-brand-border-light text-sm font-semibold cursor-pointer hover:border-brand-text transition-colors text-brand-text inline-flex items-center">
                        View Order
                    </a>
                    @if($order['status'] !== 'Cancelled')
                    <button type="button"
                            wire:click="reorder({{ $loop->index }})"
                            class="h-9 px-4 rounded-full border border-brand-border-light text-sm font-semibold cursor-pointer hover:border-brand-text transition-colors text-brand-text">
                        Reorder
                    </button>
                    @endif
                </div>

            </div>
        </div>
    @empty
        <div class="flex flex-col items-center py-12 text-center">
            <svg class="w-12 h-12 text-brand-border mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8" />
            </svg>
            <p class="text-lg font-bold text-brand-text">No orders yet</p>
            <p class="text-sm text-brand-secondary-text mt-1 mb-5">Your order history will appear here.</p>
            <a href="{{ route('home') }}" class="h-11 px-7 rounded-full bg-brand-red text-white text-sm font-semibold inline-flex items-center hover:bg-brand-dark-red transition-colors">
                Start Shopping
            </a>
        </div>
    @endforelse

</div>
