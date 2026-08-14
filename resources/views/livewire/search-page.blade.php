<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-24 md:pb-8">

    {{-- SECTION 1: Header + Search Input --}}
    <div class="mb-1">
        @if($query !== '')
            <h1 class="text-[22px] md:text-[28px] font-extrabold text-brand-text leading-tight">
                Results for &ldquo;<strong>{{ $query }}</strong>&rdquo;<span class="text-base font-normal text-brand-muted"> &middot; {{ $total }} products</span>
            </h1>
        @else
            <h1 class="text-[22px] md:text-[28px] font-extrabold text-brand-text leading-tight">
                Search our store
            </h1>
        @endif
    </div>

    <div class="relative w-full mb-4 mt-2">
        <input
            type="search"
            wire:model.live.debounce.400ms="query"
            placeholder="Search products&hellip;"
            class="w-full h-11 pl-10 pr-4 rounded-full bg-[#F5F5F5] border-0 text-sm placeholder-brand-muted focus:outline-none focus:ring-2 focus:ring-brand-light-red focus:bg-white transition"
            autocomplete="off"
            spellcheck="false"
        >
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg class="w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </span>
    </div>

    {{-- SECTION 2: Filter chips --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 md:mx-0 md:px-0 mb-5" style="scrollbar-width:none">

        {{-- Sort select chip --}}
        <div class="relative shrink-0">
            <select
                wire:model.live="sort"
                class="h-10 pl-4 pr-8 rounded-full border border-brand-border-light text-sm font-semibold cursor-pointer focus:outline-none appearance-none bg-white text-brand-text min-h-[44px]"
            >
                <option value="relevance">Popular</option>
                <option value="price-asc">Price: Low&ndash;High</option>
                <option value="price-desc">Price: High&ndash;Low</option>
                <option value="name">Name A&ndash;Z</option>
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="w-3 h-3 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </span>
        </div>

        {{-- In stock chip --}}
        <button
            wire:click="$toggle('inStockOnly')"
            class="shrink-0 h-10 min-h-[44px] px-4 rounded-full text-[13px] font-semibold transition-colors cursor-pointer {{ $inStockOnly ? 'bg-brand-text text-white' : 'bg-white border border-brand-border-light text-brand-text' }}"
        >
            In stock
        </button>

        {{-- Price preset chips --}}
        <button
            wire:click="setPreset('under50')"
            class="shrink-0 h-10 min-h-[44px] px-4 rounded-full text-[13px] font-semibold transition-colors cursor-pointer {{ $pricePreset === 'under50' ? 'bg-brand-text text-white' : 'bg-white border border-brand-border-light text-brand-text' }}"
        >
            Under GH&#x20B5; 50
        </button>

        <button
            wire:click="setPreset('50to100')"
            class="shrink-0 h-10 min-h-[44px] px-4 rounded-full text-[13px] font-semibold transition-colors cursor-pointer {{ $pricePreset === '50to100' ? 'bg-brand-text text-white' : 'bg-white border border-brand-border-light text-brand-text' }}"
        >
            GH&#x20B5; 50&ndash;100
        </button>

        <button
            wire:click="setPreset('over100')"
            class="shrink-0 h-10 min-h-[44px] px-4 rounded-full text-[13px] font-semibold transition-colors cursor-pointer {{ $pricePreset === 'over100' ? 'bg-brand-text text-white' : 'bg-white border border-brand-border-light text-brand-text' }}"
        >
            Over GH&#x20B5; 100
        </button>

        {{-- Clear all --}}
        @if($hasActiveFilters)
            <button
                wire:click="clearFilters"
                class="shrink-0 h-10 min-h-[44px] px-4 rounded-full text-[13px] font-semibold text-brand-dark-red hover:underline cursor-pointer"
            >
                Clear all
            </button>
        @endif
    </div>

    {{-- SECTION 3: Loading skeleton --}}
    <div
        wire:loading.grid
        wire:target="query, sort, inStockOnly, pricePreset, page, setPreset, clearFilters, goToPage"
        class="hidden grid-cols-2 md:grid-cols-4 gap-3 md:gap-5"
    >
        @for($i = 0; $i < 8; $i++)
            <div class="bg-white rounded-[16px] border border-brand-border-light overflow-hidden">
                <div class="skeleton w-full aspect-square"></div>
                <div class="p-3 space-y-2">
                    <div class="skeleton h-4 rounded w-3/4"></div>
                    <div class="skeleton h-3 rounded w-1/2"></div>
                    <div class="skeleton h-4 rounded w-1/3"></div>
                </div>
            </div>
        @endfor
    </div>

    {{-- SECTION 4: Results / Empty states --}}
    <div
        wire:loading.remove
        wire:target="query, sort, inStockOnly, pricePreset, page, setPreset, clearFilters, goToPage"
    >
        @if($query === '' && !$hasActiveFilters)
            {{-- Empty query default state: popular searches + categories --}}
            <p class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Popular searches</p>
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach(['Bananas', 'Milk', 'Noodles', 'Soap', 'Water', 'Rice'] as $suggestion)
                    <button
                        wire:click="setQuery('{{ $suggestion }}')"
                        class="h-10 min-h-[44px] px-4 rounded-full border border-brand-border-light text-sm font-medium text-brand-text hover:border-brand-red hover:text-brand-red transition-colors cursor-pointer"
                    >
                        {{ $suggestion }}
                    </button>
                @endforeach
            </div>

            <p class="text-[17px] font-extrabold text-brand-text mb-3 mt-6">Popular categories</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach([
                    ['slug' => 'fruits-vegetables', 'label' => 'Fruits & Veg',  'img' => 'cat-fruits.png'],
                    ['slug' => 'beverages',          'label' => 'Beverages',      'img' => 'cat-beverages.png'],
                    ['slug' => 'pantry',             'label' => 'Pantry',         'img' => 'cat-pantry.png'],
                    ['slug' => 'household',          'label' => 'Household',      'img' => 'cat-household.png'],
                ] as $cat)
                    <a
                        href="{{ route('category.show', $cat['slug']) }}"
                        class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-brand-border-light hover:border-brand-red hover:shadow-hover transition-all"
                    >
                        <img
                            src="{{ asset('images/' . $cat['img']) }}"
                            alt="{{ $cat['label'] }}"
                            class="w-10 h-10 rounded-full object-cover shrink-0"
                            loading="lazy"
                        >
                        <span class="text-sm font-bold text-brand-text">{{ $cat['label'] }}</span>
                    </a>
                @endforeach
            </div>

        @elseif($total === 0)
            {{-- Empty search results --}}
            <div class="flex flex-col items-center py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-brand-light-red flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold text-brand-text">No results for &ldquo;{{ $query }}&rdquo;</h3>
                <p class="text-sm text-brand-secondary-text mt-1 mb-6 max-w-xs">
                    Try a different word, check the spelling, or browse a category.
                </p>
                <div class="flex gap-2 justify-center flex-wrap">
                    <a href="{{ route('category.show', 'fruits-vegetables') }}"
                       class="h-10 min-h-[44px] px-5 rounded-full border border-brand-border-light text-sm font-semibold text-brand-text hover:border-brand-red hover:text-brand-red transition-colors">
                        Fruits &amp; Veg
                    </a>
                    <a href="{{ route('category.show', 'beverages') }}"
                       class="h-10 min-h-[44px] px-5 rounded-full border border-brand-border-light text-sm font-semibold text-brand-text hover:border-brand-red hover:text-brand-red transition-colors">
                        Beverages
                    </a>
                    <a href="{{ route('category.show', 'pantry') }}"
                       class="h-10 min-h-[44px] px-5 rounded-full border border-brand-border-light text-sm font-semibold text-brand-text hover:border-brand-red hover:text-brand-red transition-colors">
                        Pantry
                    </a>
                </div>
            </div>

        @else
            {{-- Product grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                @foreach($results as $item)
                    <x-storefront.product-card :product="$item" />
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($lastPage > 1)
                <div class="flex items-center gap-2 justify-center mt-8 flex-wrap">
                    @for($p = 1; $p <= $lastPage; $p++)
                        <button
                            wire:click="goToPage({{ $p }})"
                            class="w-10 h-10 min-h-[44px] rounded-full text-sm font-bold transition-colors cursor-pointer
                                {{ $page === $p
                                    ? 'bg-brand-text text-white'
                                    : 'bg-white border border-brand-border-light text-brand-text hover:border-brand-red hover:text-brand-red' }}"
                            aria-label="Page {{ $p }}"
                            aria-current="{{ $page === $p ? 'page' : 'false' }}"
                        >
                            {{ $p }}
                        </button>
                    @endfor

                    @if($page < $lastPage)
                        <button
                            wire:click="goToPage({{ $page + 1 }})"
                            class="w-10 h-10 min-h-[44px] rounded-full flex items-center justify-center bg-white border border-brand-border-light text-brand-text hover:border-brand-red hover:text-brand-red transition-colors cursor-pointer"
                            aria-label="Next page"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif
        @endif
    </div>

</div>
