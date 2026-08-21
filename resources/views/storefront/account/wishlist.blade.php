<x-layouts.storefront title="Wishlist — Fenroy">
    <div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-24 md:pb-8">

        <div class="md:flex md:gap-8">
            <x-storefront.account-sidebar active="wishlist" />
            <div class="flex-1 min-w-0">
                <livewire:account-wishlist />
            </div>
        </div>
    </div>
</x-layouts.storefront>
