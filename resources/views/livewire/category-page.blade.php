<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8" x-data="{ showFilters: false, showSort: false }">

    {{-- ═══════════════ DESKTOP PAGE HEAD (≥ md) ═══════════════ --}}
    <div class="hidden md:block">
        <nav class="text-xs text-brand-muted mb-3">
            <a href="{{ route('home') }}" class="hover:text-brand-text transition-colors">Home</a>
            <span class="mx-1">/</span>
            <a href="{{ route('category.index') }}" class="hover:text-brand-text transition-colors">Categories</a>
            <span class="mx-1">/</span>
            <span class="font-bold text-brand-text">{{ $categoryName }}</span>
        </nav>

        <div class="flex items-end justify-between gap-6 mb-2">
            <div>
                <h1 class="text-[32px] font-extrabold tracking-tight text-brand-text">
                    {{ $categoryName }}
                    <span class="text-[15px] font-normal text-brand-muted ml-1">· {{ $total }} products</span>
                </h1>
                <p class="text-sm text-brand-secondary-text mt-1">{{ $categoryDescription }}</p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                {{-- Category search --}}
                <div class="relative">
                    <input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Search in {{ $categoryName }}…"
                        class="w-60 h-[42px] pl-10 pr-4 rounded-full bg-[#F5F5F5] text-sm placeholder-brand-muted border-0 focus:outline-none focus:ring-2 focus:ring-brand-light-red focus:bg-white transition"
                    >
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-brand-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                {{-- Sort --}}
                <select
                    wire:model.live="sort"
                    class="h-[42px] pl-4 pr-9 rounded-full bg-white border border-brand-border text-sm font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-light-red appearance-none bg-[url('data:image/svg+xml;utf8,<svg fill=%22%23666%22 height=%2216%22 viewBox=%220 0 24 24%22 width=%2216%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M7 10l5 5 5-5z%22/></svg>')] bg-no-repeat bg-[right_12px_center]"
                >
                    <option value="popular">Sort: Popular</option>
                    <option value="price-asc">Price: Low → High</option>
                    <option value="price-desc">Price: High → Low</option>
                    <option value="name">Name A–Z</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ═══════════════ MOBILE PAGE HEAD (< md) ═══════════════ --}}
    <div class="md:hidden">
        <div class="flex items-center gap-3 mb-3">
            <a href="{{ route('home') }}" class="w-11 h-11 -ml-2 flex items-center justify-center rounded-full active:bg-[#F5F5F5]" aria-label="Back">
                <svg class="w-5 h-5 text-brand-text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-extrabold text-brand-text leading-tight">{{ $categoryName }}</h1>
                <p class="text-xs text-brand-muted">{{ $total }} products</p>
            </div>
        </div>

        {{-- Chip filter bar --}}
        <div class="flex gap-2 overflow-x-auto pb-3 -mx-4 px-4 scrollbar-none" style="scrollbar-width:none;">
            <button
                @click="showFilters = true"
                class="shrink-0 flex items-center gap-1.5 h-10 px-4 rounded-full text-[13px] font-semibold transition-colors cursor-pointer
                       {{ $this->activeFilterCount > 0 ? 'bg-brand-text text-white' : 'bg-white border border-brand-border text-brand-text' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                Filter{{ $this->activeFilterCount > 0 ? ' · ' . $this->activeFilterCount : '' }}
            </button>
            <button
                @click="showSort = true"
                class="shrink-0 flex items-center gap-1.5 h-10 px-4 rounded-full bg-white border border-brand-border text-[13px] font-semibold text-brand-text cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="m21 8-4-4-4 4"/><path d="M17 4v16"/></svg>
                Sort
            </button>
            <button
                wire:click="$toggle('inStockOnly')"
                class="shrink-0 h-10 px-4 rounded-full text-[13px] font-semibold transition-colors cursor-pointer
                       {{ $inStockOnly ? 'bg-brand-text text-white' : 'bg-white border border-brand-border text-brand-text' }}">
                In stock
            </button>
            <button
                wire:click="$toggle('onSaleOnly')"
                class="shrink-0 h-10 px-4 rounded-full text-[13px] font-semibold transition-colors cursor-pointer
                       {{ $onSaleOnly ? 'bg-brand-text text-white' : 'bg-white border border-brand-border text-brand-text' }}">
                On sale
            </button>
        </div>
    </div>

    {{-- ═══════════════ MAIN GRID AREA ═══════════════ --}}
    <div class="md:grid md:grid-cols-[240px_1fr] md:gap-8 mt-2 md:mt-6">

        {{-- ─── Desktop sidebar filters ─── --}}
        <aside class="hidden md:block space-y-7 self-start sticky top-24">

            {{-- Availability --}}
            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Availability</h3>
                <label class="flex items-center gap-2.5 py-1.5 cursor-pointer group">
                    <input type="checkbox" wire:model.live="inStockOnly"
                           class="w-4 h-4 rounded accent-[#E53935] cursor-pointer">
                    <span class="text-sm text-brand-text group-hover:text-brand-red transition-colors">In stock only</span>
                </label>
                <label class="flex items-center gap-2.5 py-1.5 cursor-pointer group">
                    <input type="checkbox" wire:model.live="onSaleOnly"
                           class="w-4 h-4 rounded accent-[#E53935] cursor-pointer">
                    <span class="text-sm text-brand-text group-hover:text-brand-red transition-colors">On sale</span>
                </label>
            </div>

            {{-- Price range --}}
            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Price (GH₵)</h3>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" max="200" wire:model.live.debounce.500ms="priceMin"
                           class="w-full h-10 px-3 rounded-[10px] border border-brand-input-border text-sm focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition" placeholder="Min">
                    <span class="text-brand-muted">–</span>
                    <input type="number" min="0" max="200" wire:model.live.debounce.500ms="priceMax"
                           class="w-full h-10 px-3 rounded-[10px] border border-brand-input-border text-sm focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red transition" placeholder="Max">
                </div>
            </div>

            {{-- Type --}}
            <div>
                <h3 class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-3">Type</h3>
                @foreach($typeOptions as $type => $count)
                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                    <span class="flex items-center gap-2.5">
                        <input type="checkbox"
                               wire:click="toggleType('{{ $type }}')"
                               @checked(in_array($type, $types))
                               class="w-4 h-4 rounded accent-[#E53935] cursor-pointer">
                        <span class="text-sm text-brand-text group-hover:text-brand-red transition-colors">{{ $type }}</span>
                    </span>
                    <span class="text-xs text-brand-muted">{{ $count }}</span>
                </label>
                @endforeach
            </div>

            @if($this->activeFilterCount > 0)
            <button wire:click="clearFilters"
                    class="w-full h-10 rounded-full border border-brand-border text-sm font-semibold text-brand-text hover:border-brand-text transition-colors cursor-pointer">
                Clear filters
            </button>
            @endif
        </aside>

        {{-- ─── Product grid ─── --}}
        <div>
            {{-- Skeletons while Livewire loads --}}
            <div wire:loading.grid wire:target="search, sort, inStockOnly, onSaleOnly, types, priceMin, priceMax, page, toggleType, clearFilters, goToPage"
                 class="hidden grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                @for($i = 0; $i < 8; $i++)
                <div class="bg-white rounded-[16px] border border-brand-border-light overflow-hidden">
                    <div class="skeleton pt-[100%]"></div>
                    <div class="p-3 space-y-2">
                        <div class="skeleton h-4 rounded w-3/4"></div>
                        <div class="skeleton h-3 rounded w-1/2"></div>
                        <div class="skeleton h-4 rounded w-1/3"></div>
                    </div>
                </div>
                @endfor
            </div>

            <div wire:loading.remove wire:target="search, sort, inStockOnly, onSaleOnly, types, priceMin, priceMax, page, toggleType, clearFilters, goToPage">
                @if(count($products) === 0)
                {{-- Empty state --}}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 rounded-full bg-brand-light-red flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-brand-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-brand-text mb-1">No products found</h3>
                    <p class="text-sm text-brand-secondary-text mb-5 max-w-xs">Try changing your filters or search term — or browse the full category.</p>
                    <button wire:click="clearFilters"
                            class="h-11 px-7 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold transition-colors cursor-pointer">
                        Clear all filters
                    </button>
                </div>
                @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                    @foreach($products as $product)
                    <x-storefront.product-card :product="$product" :key="$product['slug']" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($lastPage > 1)
                <div class="flex items-center justify-center gap-2 mt-8 md:mt-10">
                    @for($p = 1; $p <= $lastPage; $p++)
                    <button
                        wire:click="goToPage({{ $p }})"
                        class="w-[38px] h-[38px] md:w-[38px] md:h-[38px] min-w-[44px] min-h-[44px] md:min-w-[38px] md:min-h-[38px] rounded-full text-sm font-semibold transition-colors cursor-pointer
                               {{ $p === $page ? 'bg-brand-text text-white' : 'bg-white border border-brand-border text-brand-text hover:border-brand-text' }}">
                        {{ $p }}
                    </button>
                    @endfor
                    @if($page < $lastPage)
                    <button
                        wire:click="goToPage({{ $page + 1 }})"
                        class="h-11 md:h-[38px] px-5 rounded-full border border-brand-border text-sm font-semibold text-brand-text hover:border-brand-text transition-colors cursor-pointer">
                        Next →
                    </button>
                    @endif
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════ MOBILE FILTER DRAWER ═══════════════ --}}
    <div x-show="showFilters" x-cloak class="fixed inset-0 z-[60] md:hidden">
        <div class="absolute inset-0 bg-black/50" @click="showFilters = false" x-show="showFilters" x-transition.opacity></div>
        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl max-h-[85vh] overflow-y-auto"
             x-show="showFilters"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
            <div class="sticky top-0 bg-white flex items-center justify-between px-5 pt-4 pb-3 border-b border-brand-border-light">
                <h2 class="text-lg font-extrabold">Filters</h2>
                <button @click="showFilters = false" class="w-11 h-11 -mr-2 flex items-center justify-center rounded-full active:bg-[#F5F5F5] cursor-pointer" aria-label="Close filters">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-6 pb-28">
                {{-- Availability --}}
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-2">Availability</h3>
                    <label class="flex items-center gap-3 py-2.5 cursor-pointer min-h-[44px]">
                        <input type="checkbox" wire:model.live="inStockOnly" class="w-5 h-5 rounded accent-[#E53935]">
                        <span class="text-[15px]">In stock only</span>
                    </label>
                    <label class="flex items-center gap-3 py-2.5 cursor-pointer min-h-[44px]">
                        <input type="checkbox" wire:model.live="onSaleOnly" class="w-5 h-5 rounded accent-[#E53935]">
                        <span class="text-[15px]">On sale</span>
                    </label>
                </div>

                {{-- Price --}}
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-2">Price (GH₵)</h3>
                    <div class="flex items-center gap-2">
                        <input type="number" min="0" max="200" wire:model.live.debounce.500ms="priceMin"
                               class="w-full h-11 px-3 rounded-[10px] border border-brand-input-border text-sm focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red" placeholder="Min">
                        <span class="text-brand-muted">–</span>
                        <input type="number" min="0" max="200" wire:model.live.debounce.500ms="priceMax"
                               class="w-full h-11 px-3 rounded-[10px] border border-brand-input-border text-sm focus:outline-none focus:border-brand-red focus:ring-[3px] focus:ring-brand-light-red" placeholder="Max">
                    </div>
                </div>

                {{-- Type --}}
                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-widest text-brand-muted mb-2">Type</h3>
                    @foreach($typeOptions as $type => $count)
                    <label class="flex items-center justify-between py-2.5 cursor-pointer min-h-[44px]">
                        <span class="flex items-center gap-3">
                            <input type="checkbox"
                                   wire:click="toggleType('{{ $type }}')"
                                   @checked(in_array($type, $types))
                                   class="w-5 h-5 rounded accent-[#E53935]">
                            <span class="text-[15px]">{{ $type }}</span>
                        </span>
                        <span class="text-xs text-brand-muted">{{ $count }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Sticky drawer footer --}}
            <div class="fixed bottom-0 inset-x-0 bg-white border-t border-brand-border-light p-4 flex gap-3" x-show="showFilters">
                <button wire:click="clearFilters"
                        class="flex-1 h-12 rounded-full border border-brand-border text-sm font-semibold cursor-pointer">
                    Clear
                </button>
                <button @click="showFilters = false"
                        class="flex-[2] h-12 rounded-full bg-brand-red hover:bg-brand-dark-red text-white text-sm font-semibold cursor-pointer transition-colors">
                    Show {{ $total }} products
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════ MOBILE SORT SHEET ═══════════════ --}}
    <div x-show="showSort" x-cloak class="fixed inset-0 z-[60] md:hidden">
        <div class="absolute inset-0 bg-black/50" @click="showSort = false" x-show="showSort" x-transition.opacity></div>
        <div class="absolute bottom-0 inset-x-0 bg-white rounded-t-3xl"
             x-show="showSort"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full">
            <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-brand-border-light">
                <h2 class="text-lg font-extrabold">Sort by</h2>
                <button @click="showSort = false" class="w-11 h-11 -mr-2 flex items-center justify-center rounded-full active:bg-[#F5F5F5] cursor-pointer" aria-label="Close sort">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="p-3 pb-8">
                @foreach([
                    'popular'    => 'Popular',
                    'price-asc'  => 'Price: Low → High',
                    'price-desc' => 'Price: High → Low',
                    'name'       => 'Name A–Z',
                    'newest'     => 'Newest',
                ] as $value => $label)
                <button
                    wire:click="$set('sort', '{{ $value }}')"
                    @click="showSort = false"
                    class="w-full flex items-center justify-between px-4 py-3.5 min-h-[44px] rounded-xl text-[15px] cursor-pointer transition-colors
                           {{ $sort === $value ? 'bg-brand-light-red text-brand-dark-red font-bold' : 'text-brand-text active:bg-[#F5F5F5]' }}">
                    {{ $label }}
                    @if($sort === $value)
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
