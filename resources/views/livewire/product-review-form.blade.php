<div class="bg-white rounded-2xl border border-brand-border-light p-5">

    @if($submitted)
    <div class="text-center py-4">
        <svg class="w-10 h-10 mx-auto mb-2 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <p class="font-bold text-brand-text">Thank you for your review!</p>
        <p class="text-sm text-brand-secondary-text mt-1">Your feedback helps other shoppers.</p>
    </div>
    @else
    <h3 class="text-[15px] font-bold text-brand-text mb-4">Write a review</h3>

    <form wire:submit="submit" novalidate>

        {{-- Star rating --}}
        <div class="mb-4">
            <label class="block text-xs font-semibold text-brand-text mb-2">Your rating *</label>
            <div class="flex items-center gap-1" x-data="{ hover: 0 }">
                @for($i = 1; $i <= 5; $i++)
                <button type="button"
                        wire:click="setRating({{ $i }})"
                        @mouseover="hover = {{ $i }}"
                        @mouseleave="hover = 0"
                        class="cursor-pointer transition-transform hover:scale-110"
                        aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}">
                    <svg class="w-7 h-7 {{ $i <= $rating ? 'text-amber-400' : 'text-brand-border' }}"
                         :class="{ 'text-amber-400': hover >= {{ $i }}, 'text-brand-border': hover < {{ $i }} && {{ $i }} > {{ $rating }} }"
                         fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </button>
                @endfor
            </div>
        </div>

        {{-- Name --}}
        <div class="mb-3">
            <label class="block text-xs font-semibold text-brand-text mb-1">Your name *</label>
            <input wire:model="reviewer_name" type="text" placeholder="Kofi Mensah"
                   class="w-full h-10 px-3 rounded-xl border border-brand-border-light text-sm focus:outline-none focus:ring-2 focus:ring-brand-light-red @error('reviewer_name') border-red-400 @enderror">
            @error('reviewer_name') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
        </div>

        {{-- Body --}}
        <div class="mb-4">
            <label class="block text-xs font-semibold text-brand-text mb-1">Your review <span class="text-brand-muted font-normal">(optional)</span></label>
            <textarea wire:model="body" rows="3" placeholder="What did you love about this product?"
                      class="w-full px-3 py-2.5 rounded-xl border border-brand-border-light text-sm focus:outline-none focus:ring-2 focus:ring-brand-light-red resize-none @error('body') border-red-400 @enderror"></textarea>
            @error('body') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-70 cursor-wait"
                class="h-10 px-5 bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
            <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            Submit review
        </button>
    </form>
    @endif

</div>
