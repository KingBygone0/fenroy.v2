<div>

    @if (count($items) > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
            @foreach ($items as $index => $item)
                <div wire:key="wishlist-{{ $index }}" class="flex flex-col gap-2">
                    <x-storefront.product-card :product="$item" />
                    <button
                        type="button"
                        wire:click="moveToCart({{ $index }})"
                        class="w-full h-10 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-[13px] font-semibold transition-colors cursor-pointer"
                    >
                        Add to cart
                    </button>
                    <button
                        type="button"
                        wire:click="removeItem({{ $index }})"
                        class="w-full h-10 rounded-full border border-brand-border-light text-[13px] font-semibold text-brand-secondary-text hover:text-brand-danger hover:border-brand-danger transition-colors cursor-pointer"
                    >
                        Remove
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-brand-border mb-3 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
            <p class="text-lg font-bold text-brand-text">Your wishlist is empty</p>
            <p class="text-sm text-brand-secondary-text mt-1 mb-5">Save items you love and find them here.</p>
            <a
                href="{{ route('category.index') }}"
                class="h-11 px-7 rounded-full bg-brand-red text-white text-sm font-semibold inline-flex items-center hover:bg-brand-dark-red transition-colors"
            >
                Browse Products
            </a>
        </div>
    @endif

</div>
