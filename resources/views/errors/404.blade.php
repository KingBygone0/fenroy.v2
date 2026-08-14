<x-layouts.storefront title="Page Not Found — Fenroy">
<div class="min-h-[60vh] flex flex-col items-center justify-center px-4 py-16 text-center">

    <div class="w-20 h-20 rounded-full bg-brand-light-red flex items-center justify-center mb-6">
        <svg class="w-9 h-9 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
        </svg>
    </div>

    <h1 class="text-[72px] font-extrabold text-brand-text leading-none tracking-tight">404</h1>
    <p class="text-xl font-bold text-brand-text mt-2">Page not found</p>
    <p class="text-sm text-brand-secondary-text mt-2 mb-8 max-w-sm">
        The page you're looking for doesn't exist or may have been moved. Let's get you back on track.
    </p>

    <div class="flex items-center gap-3 flex-wrap justify-center">
        <a href="{{ route('home') }}"
           class="h-12 px-7 rounded-full bg-brand-red hover:bg-brand-dark-red text-white font-semibold text-sm transition-colors inline-flex items-center cursor-pointer">
            Go to Homepage
        </a>
        <a href="{{ route('category.index') }}"
           class="h-12 px-7 rounded-full border border-brand-border-light text-brand-text hover:border-brand-text font-semibold text-sm transition-colors inline-flex items-center cursor-pointer">
            Browse Categories
        </a>
    </div>

    <p class="text-sm text-brand-muted mt-10">
        Need help?
        <a href="#" class="text-brand-red hover:underline font-semibold">Contact us</a>
    </p>
</div>
</x-layouts.storefront>
