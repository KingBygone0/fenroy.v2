<x-layouts.storefront title="Today's Deals — Fenroy">
<div class="max-w-[1280px] mx-auto px-4 md:px-14 py-4 md:py-8 pb-24 md:pb-8">

    {{-- Hero strip --}}
    <div class="bg-brand-red rounded-2xl px-6 py-8 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-white text-2xl md:text-3xl font-extrabold">Today's Deals</h1>
            <p class="text-white/75 text-sm mt-1">Limited-time offers on your favourites. Updated daily.</p>
        </div>
        <div class="bg-white/15 rounded-full px-5 py-2 text-white text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>Ends in</span>
            <span
                x-data="{ h: 11, m: 47, s: 30 }"
                x-init="setInterval(function(){ if(s>0){s--}else if(m>0){m--;s=59}else if(h>0){h--;m=59;s=59} }, 1000)"
                x-text="String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0')"
            ></span>
        </div>
    </div>

    {{-- Flash Deals section --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-[17px] font-extrabold">Flash Deals</h2>
        <span class="text-sm text-brand-muted">20 deals</span>
    </div>

    @php
    $deals = [
        ['name' => 'Cavendish Bananas',    'unit' => '1 bunch',       'price' => 14.00, 'old_price' => 18.00,  'stock' => 24, 'slug' => 'cavendish-bananas',    'image' => \App\Support\ProductImages::get('cavendish-bananas')],
        ['name' => 'Milo Chocolate Drink', 'unit' => '500g tin',      'price' => 56.00, 'old_price' => 62.00,  'stock' => 12, 'slug' => 'milo-chocolate-drink', 'image' => \App\Support\ProductImages::get('milo-chocolate-drink')],
        ['name' => 'Cowbell Powdered Milk','unit' => '400g tin',      'price' => 42.00, 'old_price' => 48.00,  'stock' => 3,  'slug' => 'cowbell-powdered-milk', 'image' => \App\Support\ProductImages::get('cowbell-powdered-milk')],
        ['name' => 'Dettol Soap 4-Pack',   'unit' => '4x75g',         'price' => 35.00, 'old_price' => 40.00,  'stock' => 18, 'slug' => 'dettol-soap-4-pack',   'image' => \App\Support\ProductImages::get('dettol-soap-4-pack')],
        ['name' => 'Pineapple (Sugarloaf)','unit' => '1 whole',       'price' => 16.00, 'old_price' => 20.00,  'stock' => 8,  'slug' => 'pineapple-sugarloaf',  'image' => \App\Support\ProductImages::get('pineapple-sugarloaf')],
        ['name' => 'Canola Cooking Oil',   'unit' => '2L bottle',     'price' => 64.00, 'old_price' => 72.00,  'stock' => 9,  'slug' => 'canola-cooking-oil',   'image' => \App\Support\ProductImages::get('canola-cooking-oil')],
        ['name' => 'Red Onions',           'unit' => '1kg bag',       'price' => 18.00, 'old_price' => 22.00,  'stock' => 30, 'slug' => 'red-onions',           'image' => \App\Support\ProductImages::get('red-onions')],
        ['name' => 'Pampers Baby Diapers', 'unit' => 'Size 4, 50ct',  'price' => 120.00,'old_price' => 135.00, 'stock' => 5,  'slug' => 'pampers-baby-diapers', 'image' => \App\Support\ProductImages::get('pampers-baby-diapers')],
    ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
        @foreach($deals as $product)
            <x-storefront.product-card :product="$product" />
        @endforeach
    </div>

    {{-- More Savings section --}}
    <div class="flex items-center justify-between mt-8 mb-4">
        <h2 class="text-[17px] font-extrabold">More Savings</h2>
        <span class="text-sm text-brand-muted">12 deals</span>
    </div>

    @php
    $moreSavings = [
        ['name' => 'Titus Sardines',         'unit' => '125g tin',  'price' => 12.00, 'old_price' => 14.00, 'stock' => 20, 'slug' => 'titus-sardines',         'image' => \App\Support\ProductImages::get('titus-sardines')],
        ['name' => 'Ginger Root',            'unit' => '250g',      'price' => 6.50,  'old_price' => 8.00,  'stock' => 22, 'slug' => 'ginger-root',            'image' => \App\Support\ProductImages::get('ginger-root')],
        ['name' => 'Golden Tree Chocolate',  'unit' => '100g bar',  'price' => 19.00, 'old_price' => 23.00, 'stock' => 15, 'slug' => 'golden-tree-chocolate',  'image' => \App\Support\ProductImages::get('golden-tree-chocolate')],
        ['name' => 'Omo Detergent',          'unit' => '1kg bag',   'price' => 38.00, 'old_price' => 45.00, 'stock' => 11, 'slug' => 'omo-detergent',          'image' => \App\Support\ProductImages::get('omo-detergent')],
    ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-5">
        @foreach($moreSavings as $product)
            <x-storefront.product-card :product="$product" />
        @endforeach
    </div>

</div>
</x-layouts.storefront>
