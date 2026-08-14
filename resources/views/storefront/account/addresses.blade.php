<x-layouts.storefront title="Addresses — Fenroy">
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-24 md:pb-8">
    <div class="md:flex md:gap-8">
        <x-storefront.account-sidebar active="addresses" />
        <div class="flex-1 min-w-0 mt-4 md:mt-0">
            <h1 class="text-lg font-extrabold hidden md:block mb-5">Delivery Addresses</h1>
            <livewire:account-addresses />
        </div>
    </div>
</div>
</x-layouts.storefront>
