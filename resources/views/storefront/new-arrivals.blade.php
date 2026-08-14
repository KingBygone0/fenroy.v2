<x-layouts.storefront title="New Arrivals — Fenroy">
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-24 md:pb-8">

    {{-- Hero banner --}}
    <div class="flex items-center gap-3 mb-6">
        <svg class="w-8 h-8 text-brand-red flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
        </svg>
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold">New Arrivals</h1>
            <p class="text-sm text-brand-secondary-text mt-0.5">The latest additions to the Fenroy store.</p>
        </div>
    </div>

    {{-- Overline divider --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-[11px] font-bold tracking-widest text-brand-muted uppercase">Just Added</span>
        <div class="flex-1 h-px bg-brand-border-light"></div>
    </div>

    @php
    $newArrivals = [
        ['name' => 'Akabanga Hot Sauce',       'unit' => '100ml',       'price' => 22.00, 'old_price' => null,  'stock' => 14, 'slug' => 'akabanga-hot-sauce',       'image' => \App\Support\ProductImages::get('akabanga-hot-sauce')],
        ['name' => 'Golden Tree Chocolate',    'unit' => '100g bar',    'price' => 19.00, 'old_price' => null,  'stock' => 22, 'slug' => 'golden-tree-chocolate',    'image' => \App\Support\ProductImages::get('golden-tree-chocolate')],
        ['name' => 'Close-Up Toothpaste',      'unit' => '75ml tube',   'price' => 11.50, 'old_price' => null,  'stock' => 30, 'slug' => 'close-up-toothpaste',      'image' => \App\Support\ProductImages::get('close-up-toothpaste')],
        ['name' => 'Sunlight Dish Liquid',     'unit' => '750ml',       'price' => 18.00, 'old_price' => null,  'stock' => 2,  'slug' => 'sunlight-dish-liquid',     'image' => \App\Support\ProductImages::get('sunlight-dish-liquid')],
        ['name' => 'Titus Sardines',           'unit' => '125g tin',    'price' => 12.00, 'old_price' => 14.00, 'stock' => 7,  'slug' => 'titus-sardines',           'image' => \App\Support\ProductImages::get('titus-sardines')],
        ['name' => 'Cowpea (Black-Eye Beans)', 'unit' => '1kg bag',     'price' => 23.00, 'old_price' => 26.00, 'stock' => 0,  'slug' => 'cowpea-black-eye-beans',   'image' => \App\Support\ProductImages::get('cowpea-black-eye-beans')],
        ['name' => 'Ginger Root',              'unit' => '250g',        'price' => 6.50,  'old_price' => 8.00,  'stock' => 22, 'slug' => 'ginger-root',              'image' => \App\Support\ProductImages::get('ginger-root')],
        ['name' => 'Indomie Noodles (10 pk)',  'unit' => '10x70g',      'price' => 28.00, 'old_price' => null,  'stock' => 30, 'slug' => 'indomie-noodles-10-pk',    'image' => \App\Support\ProductImages::get('indomie-noodles-10-pk')],
    ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
        @foreach($newArrivals as $product)
            <x-storefront.product-card :product="$product" />
        @endforeach
    </div>

</div>
</x-layouts.storefront>
