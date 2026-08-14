<div>
    {{-- Centered hero --}}
    <div class="text-center mb-10">
        <h1 class="text-[32px] md:text-[38px] font-extrabold tracking-[-0.02em] text-brand-text mb-6">How can we help?</h1>

        {{-- Search --}}
        <div class="max-w-[520px] mx-auto mb-6 relative">
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder='Search questions — e.g. "track my order"'
                class="w-full h-12 pl-10 pr-5 rounded-full bg-[#F5F5F5] text-[14px] text-brand-text placeholder-brand-muted border-0 focus:outline-none focus:ring-2 focus:ring-brand-light-red focus:bg-white transition"
            >
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            @if($search)
            <button wire:click="$set('search', '')" class="absolute right-4 top-1/2 -translate-y-1/2 text-brand-muted hover:text-brand-text">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            @endif
        </div>

        {{-- Topic chips --}}
        <div class="flex flex-wrap justify-center gap-2">
            @foreach(['all' => 'All', 'orders' => 'Orders', 'payments' => 'Payments', 'delivery' => 'Delivery', 'returns' => 'Returns', 'products' => 'Products', 'account' => 'Account'] as $key => $label)
            <button wire:click="setTopic('{{ $key }}')"
                    class="h-9 px-5 rounded-full text-[13px] font-medium border transition-colors
                           {{ $activeTopic === $key
                               ? 'bg-brand-text text-white border-brand-text'
                               : 'bg-white border-[#E5E5E5] text-brand-secondary-text hover:border-brand-text hover:text-brand-text' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Results --}}
    <div class="max-w-[760px] mx-auto">
        @if(count($this->groups) === 0)
        <div class="text-center py-16">
            <p class="text-[16px] font-semibold text-brand-text mb-1">No answers matched</p>
            <p class="text-[14px] text-brand-secondary-text mb-6">Try different words, or ask us directly on WhatsApp.</p>
            <a href="https://wa.me/233551234567" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 h-10 px-6 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors">
                Ask us on WhatsApp
            </a>
        </div>
        @else
        <div class="flex flex-col gap-8">
            @foreach($this->groups as $group)
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">{{ $group['label'] }}</p>
                <div class="border border-[#E5E5E5] rounded-2xl overflow-hidden">
                    @foreach($group['items'] as $item)
                    <div id="{{ $item['id'] }}"
                         class="border-b border-[#F0F0F0] last:border-b-0 transition-colors {{ $openQuestion === $item['id'] ? 'bg-[#FFF9F9]' : 'hover:bg-[#FAFAFA]' }}">
                        <button wire:click="toggle('{{ $item['id'] }}')"
                                class="w-full flex items-center justify-between px-5 py-4 text-left gap-4"
                                aria-expanded="{{ $openQuestion === $item['id'] ? 'true' : 'false' }}">
                            <span class="text-[15px] font-semibold text-brand-text">{{ $item['q'] }}</span>
                            <span class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition-colors
                                         {{ $openQuestion === $item['id'] ? 'bg-brand-red text-white' : 'bg-[#F5F5F5] text-[#666]' }}">
                                @if($openQuestion === $item['id'])
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                @endif
                            </span>
                        </button>
                        @if($openQuestion === $item['id'])
                        <div class="px-5 pb-5">
                            <p class="text-[14px] text-[#555] leading-[1.7] max-w-[620px]">{{ $item['a'] }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Still need help --}}
            <div class="bg-[#F9F9F9] rounded-2xl px-7 py-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <p class="text-[16px] font-bold text-brand-text">Still need help?</p>
                    <p class="text-[13px] text-brand-secondary-text mt-0.5">Our team replies within minutes, 8am–9pm daily.</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="https://wa.me/233551234567" target="_blank" rel="noopener"
                       class="inline-flex items-center h-10 px-5 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-[13px] font-semibold transition-colors whitespace-nowrap">
                        Chat on WhatsApp
                    </a>
                    <a href="{{ route('contact') }}"
                       class="inline-flex items-center h-10 px-5 rounded-full border border-[#E5E5E5] text-brand-secondary-text text-[13px] font-medium hover:border-brand-text hover:text-brand-text transition-colors whitespace-nowrap">
                        Contact us
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
