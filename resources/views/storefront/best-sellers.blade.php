<x-layouts.storefront title="Best Sellers — Fenroy">
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-24 md:pb-8">

    {{-- Hero banner --}}
    <div class="flex items-center gap-3 mb-6">
        <svg class="w-8 h-8 text-brand-red flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7l-4 4v6l-4 1-4-1v-6L6 9z"/>
        </svg>
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold">Best Sellers</h1>
            <p class="text-sm text-brand-secondary-text mt-0.5">Customer favourites, week after week.</p>
        </div>
    </div>

    {{-- Overline divider --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-[11px] font-bold tracking-widest text-brand-muted uppercase">Most Ordered</span>
        <div class="flex-1 h-px bg-brand-border-light"></div>
    </div>

    @php
    $bestSellers = [
        ['name' => 'Roma Tomatoes',          'unit' => '1kg bag',   'price' => 16.00, 'old_price' => null,  'stock' => 40, 'slug' => 'roma-tomatoes',          'image' => \App\Support\ProductImages::get('roma-tomatoes')],
        ['name' => 'Milo Chocolate Drink',   'unit' => '500g tin',  'price' => 56.00, 'old_price' => 62.00, 'stock' => 12, 'slug' => 'milo-chocolate-drink',   'image' => \App\Support\ProductImages::get('milo-chocolate-drink')],
        ['name' => 'Indomie Noodles (10 pk)','unit' => '10x70g',    'price' => 28.00, 'old_price' => null,  'stock' => 30, 'slug' => 'indomie-noodles-10-pk',  'image' => \App\Support\ProductImages::get('indomie-noodles-10-pk')],
        ['name' => 'Dettol Soap 4-Pack',     'unit' => '4x75g',     'price' => 35.00, 'old_price' => 40.00, 'stock' => 18, 'slug' => 'dettol-soap-4-pack',     'image' => \App\Support\ProductImages::get('dettol-soap-4-pack')],
        ['name' => 'Omo Detergent',          'unit' => '1kg bag',   'price' => 38.00, 'old_price' => null,  'stock' => 11, 'slug' => 'omo-detergent',          'image' => \App\Support\ProductImages::get('omo-detergent')],
        ['name' => 'Voltic Still Water',     'unit' => '1.5L',      'price' => 7.00,  'old_price' => null,  'stock' => 50, 'slug' => 'voltic-still-water',     'image' => \App\Support\ProductImages::get('voltic-still-water')],
        ['name' => 'Cowbell Powdered Milk',  'unit' => '400g tin',  'price' => 42.00, 'old_price' => 48.00, 'stock' => 3,  'slug' => 'cowbell-powdered-milk',  'image' => \App\Support\ProductImages::get('cowbell-powdered-milk')],
        ['name' => 'Red Onions',             'unit' => '1kg bag',   'price' => 18.00, 'old_price' => 22.00, 'stock' => 30, 'slug' => 'red-onions',             'image' => \App\Support\ProductImages::get('red-onions')],
    ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
        @foreach($bestSellers as $product)
            <x-storefront.product-card :product="$product" />
        @endforeach
    </div>

</div>
</x-layouts.storefront>
