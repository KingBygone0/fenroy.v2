<div>
    @if($sent)
        <div class="text-center py-16">
            <div class="w-14 h-14 rounded-full bg-brand-success-tint flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-brand-success" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="text-[18px] font-bold text-brand-text mb-2">Message sent</h3>
            <p class="text-[14px] text-brand-secondary-text">We'll reply to <strong>{{ $email }}</strong> within a few hours.</p>
        </div>
    @else
        <h2 class="text-[20px] font-bold text-brand-text mb-1">Send us a message</h2>
        <p class="text-[13px] text-brand-secondary-text mb-6">We'll get back to you by email or phone.</p>

        <form wire:submit="send" novalidate>

            {{-- Name + Phone --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[13px] font-medium text-brand-text mb-1.5">Full name</label>
                    <input wire:model="name" type="text" placeholder="Ama Owusu"
                           class="w-full h-11 px-4 rounded-[10px] bg-white border border-[#D4D4D4] text-[14px] text-brand-text placeholder-brand-muted focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition @error('name') border-brand-danger @enderror">
                    @error('name')<p class="mt-1 text-[12px] text-brand-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[13px] font-medium text-brand-text mb-1.5">Phone</label>
                    <input wire:model="phone" type="tel" placeholder="024 000 0000"
                           class="w-full h-11 px-4 rounded-[10px] bg-white border border-[#D4D4D4] text-[14px] text-brand-text placeholder-brand-muted focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition @error('phone') border-brand-danger @enderror">
                    @error('phone')<p class="mt-1 text-[12px] text-brand-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-brand-text mb-1.5">Email</label>
                <input wire:model="email" type="email" placeholder="you@example.com"
                       class="w-full h-11 px-4 rounded-[10px] bg-white border border-[#D4D4D4] text-[14px] text-brand-text placeholder-brand-muted focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition @error('email') border-brand-danger @enderror">
                @error('email')<p class="mt-1 text-[12px] text-brand-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Topic chips --}}
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-brand-text mb-2">Topic</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['My order', 'Payments', 'Delivery', 'A product', 'Something else'] as $t)
                    <button type="button" wire:click="$set('topic', '{{ $t }}')"
                            class="h-9 px-4 rounded-full text-[13px] font-medium border transition-colors
                                   {{ $topic === $t
                                       ? 'bg-brand-text text-white border-brand-text'
                                       : 'bg-white border-[#E5E5E5] text-brand-secondary-text hover:border-brand-text hover:text-brand-text' }}">
                        {{ $t }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Order number --}}
            <div class="mb-4">
                <label class="block text-[13px] font-medium text-brand-text mb-1.5">
                    Order number <span class="font-normal text-brand-muted">(optional)</span>
                </label>
                <input wire:model="order_number" type="text" placeholder="FN-2481"
                       class="w-full h-11 px-4 rounded-[10px] bg-white border border-[#D4D4D4] text-[14px] text-brand-text placeholder-brand-muted focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition">
            </div>

            {{-- Message --}}
            <div class="mb-6">
                <label class="block text-[13px] font-medium text-brand-text mb-1.5">Message</label>
                <textarea wire:model="message" rows="4" placeholder="Tell us how we can help…"
                          class="w-full px-4 py-3 rounded-[10px] bg-white border border-[#D4D4D4] text-[14px] text-brand-text placeholder-brand-muted focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition resize-none @error('message') border-brand-danger @enderror"></textarea>
                @error('message')<p class="mt-1 text-[12px] text-brand-danger">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-wait"
                    class="w-full h-12 rounded-full bg-brand-red hover:bg-brand-dark-red text-white font-semibold text-[15px] transition-colors flex items-center justify-center gap-2">
                <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                <span wire:loading.remove>Send Message</span>
                <span wire:loading>Sending…</span>
            </button>
            <p class="text-center text-[12px] text-brand-muted mt-3">We typically reply within 2 hours during opening times.</p>
        </form>
    @endif
</div>
