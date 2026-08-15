<x-layouts.storefront title="My Profile — Fenroy">
    <div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-24 md:pb-8">

        <div class="md:flex md:gap-8">
            <x-storefront.account-sidebar active="profile" />
            <div class="flex-1 min-w-0">
                <livewire:account-profile />
            </div>
        </div>
    </div>
</x-layouts.storefront>
