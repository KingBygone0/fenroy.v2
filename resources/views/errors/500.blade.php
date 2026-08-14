<x-layouts.storefront title="Something Went Wrong — Fenroy">
<div class="min-h-[60vh] flex flex-col items-center justify-center px-4 py-16 text-center">

    <div class="w-20 h-20 rounded-full bg-[#FFF7E6] flex items-center justify-center mb-6">
        <svg class="w-9 h-9 text-[#B45309]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
        </svg>
    </div>

    <h1 class="text-[72px] font-extrabold text-brand-text leading-none tracking-tight">500</h1>
    <p class="text-xl font-bold text-brand-text mt-2">Something went wrong</p>
    <p class="text-sm text-brand-secondary-text mt-2 mb-8 max-w-sm">
        We hit an unexpected error. Our team has been notified. Please try again in a moment.
    </p>

    <div class="flex items-center gap-3 flex-wrap justify-center">
        <a href="{{ route('home') }}"
           class="h-12 px-7 rounded-full bg-brand-red hover:bg-brand-dark-red text-white font-semibold text-sm transition-colors inline-flex items-center cursor-pointer">
            Go to Homepage
        </a>
        <button onclick="window.location.reload()"
                class="h-12 px-7 rounded-full border border-brand-border-light text-brand-text hover:border-brand-text font-semibold text-sm transition-colors inline-flex items-center cursor-pointer">
            Try again
        </button>
    </div>
</div>
</x-layouts.storefront>
