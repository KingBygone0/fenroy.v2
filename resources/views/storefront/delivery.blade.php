<x-layouts.storefront title="Delivery Information — Fenroy">
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-8 md:py-10">

    {{-- Breadcrumb --}}
    <nav class="text-[12px] text-brand-muted mb-5 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">Home</a>
        <span>/</span>
        <span class="text-brand-text">Delivery</span>
    </nav>

    <h1 class="text-[32px] md:text-[38px] font-extrabold tracking-[-0.02em] text-brand-text leading-tight mb-3">
        Delivery information
    </h1>
    <p class="text-[15px] text-brand-secondary-text leading-[1.7] mb-10" style="max-width:600px;">
        We deliver across Kumasi and its environs seven days a week. Order before 6pm for same-day delivery, or pick a window that suits you at checkout.
    </p>

    {{-- Two-col --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">

        {{-- Left: Zones & fees --}}
        <div>
            <h2 class="text-[16px] font-bold text-brand-text mb-4">Zones & fees</h2>

            {{-- Desktop table --}}
            <div class="hidden md:block">
                <div class="grid grid-cols-3 pb-2 mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-brand-muted">Zone</span>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-brand-muted">Fee</span>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-brand-muted">Free above</span>
                </div>
                @foreach($zones as $zone)
                <div class="grid grid-cols-3 py-3 border-t border-[#F0F0F0]">
                    <span class="text-[14px] text-brand-text">{{ $zone->name }}</span>
                    <span class="text-[14px] text-brand-text">GH₵ {{ number_format($zone->fee, 2) }}</span>
                    <span class="text-[14px] font-semibold {{ $zone->free_above ? 'text-brand-success' : 'text-brand-muted' }}">
                        {{ $zone->free_above ? 'GH₵ ' . number_format($zone->free_above, 0) : '—' }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Mobile stacked --}}
            <div class="md:hidden flex flex-col gap-3">
                @foreach($zones as $zone)
                <div class="border border-[#E5E5E5] rounded-xl p-4">
                    <p class="text-[14px] font-semibold text-brand-text mb-2">{{ $zone->name }}</p>
                    <div class="flex justify-between text-[13px]">
                        <span class="text-brand-secondary-text">Fee</span>
                        <span class="text-brand-text">GH₵ {{ number_format($zone->fee, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-[13px] mt-1">
                        <span class="text-brand-secondary-text">Free above</span>
                        <span class="font-semibold {{ $zone->free_above ? 'text-brand-success' : 'text-brand-muted' }}">
                            {{ $zone->free_above ? 'GH₵ ' . number_format($zone->free_above, 0) : '—' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            <p class="text-[12px] text-brand-secondary-text mt-4">
                Minimum basket GH₵ 50.00 for all zones. Zone fees are shown again at checkout before you pay.
            </p>
        </div>

        {{-- Right: Windows + Good to know --}}
        <div class="flex flex-col gap-6">

            {{-- Delivery windows --}}
            <div>
                <h2 class="text-[16px] font-bold text-brand-text mb-4">Delivery windows</h2>
                <div class="flex flex-col">
                    @foreach([
                        ['label' => 'Morning',   'time' => '9:00am – 12:00pm'],
                        ['label' => 'Afternoon', 'time' => '12:00pm – 3:00pm'],
                        ['label' => 'Evening',   'time' => '4:00pm – 7:00pm'],
                    ] as $win)
                    <div class="flex items-center justify-between py-3 border-b border-[#F0F0F0] text-[14px]">
                        <span class="font-medium text-brand-text">{{ $win['label'] }}</span>
                        <span class="text-brand-secondary-text">{{ $win['time'] }}</span>
                    </div>
                    @endforeach
                    <div class="flex items-center justify-between pt-3 text-[14px]">
                        <span class="font-semibold text-brand-dark-red">Same-day cut-off</span>
                        <span class="font-semibold text-brand-dark-red">Order by 6:00pm</span>
                    </div>
                </div>
            </div>

            {{-- Good to know --}}
            <div>
                <h2 class="text-[16px] font-bold text-brand-text mb-4">Good to know</h2>
                <ul class="flex flex-col gap-3">
                    @foreach([
                        ['color' => '#2E7D32', 'bg' => '#E8F5E9', 'text' => 'Frozen and chilled items travel in cooler bags.'],
                        ['color' => '#1565C0', 'bg' => '#E3F2FD', 'text' => 'Your rider calls when they\'re 10 minutes away.'],
                        ['color' => '#E65100', 'bg' => '#FFF3E0', 'text' => 'Damaged or wrong items? Report within 24 hours for a refund or replacement.'],
                        ['color' => '#616161', 'bg' => '#F5F5F5', 'text' => 'Add a landmark to your address — it helps riders find you faster.'],
                    ] as $tip)
                    <li class="flex gap-3 text-[14px] text-brand-secondary-text leading-[1.6]">
                        <span class="shrink-0 w-5 h-5 rounded flex items-center justify-center mt-0.5" style="background: {{ $tip['bg'] }}">
                            <span class="w-2 h-2 rounded-sm" style="background: {{ $tip['color'] }}"></span>
                        </span>
                        <span>{{ $tip['text'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- WhatsApp closing band --}}
    <div class="bg-brand-light-red rounded-2xl px-7 py-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <p class="text-[17px] font-bold text-brand-dark-red">Not sure if we deliver to you?</p>
            <p class="text-[14px] text-[#8A5A5A] mt-1">Select your area at checkout or ask us on WhatsApp.</p>
        </div>
        <a href="https://wa.me/233551234567" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 h-11 px-6 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-[14px] font-semibold transition-colors whitespace-nowrap">
            Chat on WhatsApp
        </a>
    </div>

</div>
</x-layouts.storefront>
