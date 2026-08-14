<x-layouts.storefront title="All Categories — Fenroy">
    <div class="max-w-[1280px] mx-auto px-4 md:px-14 py-6 md:py-10">

        {{-- Desktop breadcrumb --}}
        <nav class="hidden md:block text-xs text-brand-muted mb-3">
            <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">Home</a>
            <span class="mx-1">/</span>
            <span class="font-bold text-brand-text">Categories</span>
        </nav>

        {{-- Mobile back header --}}
        <div class="md:hidden flex items-center gap-3 mb-4">
            <a href="{{ route('home') }}" class="w-11 h-11 -ml-2 flex items-center justify-center rounded-full active:bg-[#F5F5F5]" aria-label="Back">
                <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <h1 class="text-lg font-extrabold text-brand-text">All Categories</h1>
        </div>

        <h1 class="hidden md:block text-[32px] font-extrabold tracking-tight text-brand-text mb-8">All Categories</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
            @foreach($categories as $cat)
            <a href="{{ route('category.show', $cat['slug']) }}"
               class="group flex items-center gap-4 p-4 md:p-5 bg-white border border-brand-border-light rounded-2xl hover:border-brand-red hover:shadow-hover transition-all duration-150 cursor-pointer">
                <img src="{{ asset('images/' . $cat['image']) }}" alt="{{ $cat['name'] }}"
                     class="w-14 h-14 rounded-full object-cover shrink-0">
                <div>
                    <p class="text-sm font-bold text-brand-text group-hover:text-brand-red transition-colors">{{ $cat['name'] }}</p>
                    <p class="text-xs text-brand-muted mt-0.5">{{ $cat['count'] }} products</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</x-layouts.storefront>
