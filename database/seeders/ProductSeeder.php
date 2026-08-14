<?php

namespace Database\Seeders;

use App\Support\ProductImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->delete();

        $now = now();
        $products = [
            // Fruits & Vegetables
            ['name' => 'Cavendish Bananas',       'slug' => 'cavendish-bananas',       'sku' => 'FRV-0012', 'category' => 'fruits-vegetables', 'type' => 'Fruits',     'unit' => '1 bunch (~6 pcs)',  'price' => 14.00,  'old_price' => 18.00,  'stock' => 24, 'is_featured' => true,  'is_best_seller' => false, 'description' => 'Sweet, ripe Cavendish bananas sourced fresh from farms in the Eastern Region.'],
            ['name' => 'Roma Tomatoes',            'slug' => 'roma-tomatoes',           'sku' => 'FRV-0021', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '500g pack',         'price' => 9.50,   'old_price' => null,   'stock' => 15, 'is_featured' => false, 'is_best_seller' => true,  'description' => 'Firm, flavourful Roma tomatoes perfect for sauces, stews and salads.'],
            ['name' => 'Pineapple (Sugarloaf)',    'slug' => 'pineapple-sugarloaf',     'sku' => 'FRV-0033', 'category' => 'fruits-vegetables', 'type' => 'Fruits',     'unit' => '1 whole',           'price' => 16.00,  'old_price' => 20.00,  'stock' => 8,  'is_featured' => true,  'is_best_seller' => false, 'description' => 'Sweet Ghanaian sugarloaf pineapple, hand-picked for peak ripeness.'],
            ['name' => 'Red Onions',               'slug' => 'red-onions',              'sku' => 'FRV-0041', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '1kg bag',           'price' => 18.00,  'old_price' => 22.00,  'stock' => 30, 'is_featured' => false, 'is_best_seller' => true,  'description' => 'Fresh red onions, a kitchen essential for every Ghanaian dish.'],
            ['name' => 'Carrots',                  'slug' => 'carrots',                 'sku' => 'FRV-0052', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '500g pack',         'price' => 9.00,   'old_price' => 11.00,  'stock' => 18, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Crunchy, sweet carrots great for soups, stews and juicing.'],
            ['name' => 'Ginger Root',              'slug' => 'ginger-root',             'sku' => 'FRV-0063', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '250g',              'price' => 6.50,   'old_price' => 8.00,   'stock' => 22, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Fresh ginger root, perfect for cooking, teas and natural remedies.'],
            ['name' => 'Garden Eggs',              'slug' => 'garden-eggs',             'sku' => 'FRV-0074', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '500g pack',         'price' => 12.00,  'old_price' => null,   'stock' => 3,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Fresh white garden eggs, a staple in Ghanaian cuisine.'],
            ['name' => 'Watermelon',               'slug' => 'watermelon',              'sku' => 'FRV-0085', 'category' => 'fruits-vegetables', 'type' => 'Fruits',     'unit' => '1 whole (~4kg)',    'price' => 25.00,  'old_price' => null,   'stock' => 6,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Juicy, refreshing watermelon — perfect for the Accra heat.'],
            ['name' => 'Pawpaw (Papaya)',          'slug' => 'pawpaw-papaya',           'sku' => 'FRV-0096', 'category' => 'fruits-vegetables', 'type' => 'Fruits',     'unit' => '1 whole',           'price' => 15.00,  'old_price' => null,   'stock' => 12, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Ripe, sweet pawpaw rich in vitamins and great for breakfast.'],
            ['name' => 'Scotch Bonnet Peppers',   'slug' => 'scotch-bonnet-peppers',   'sku' => 'FRV-0107', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '200g pack',         'price' => 7.50,   'old_price' => null,   'stock' => 25, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Fresh Ghanaian scotch bonnet peppers for that essential heat in your dishes.'],
            ['name' => 'Kontomire Leaves',        'slug' => 'kontomire-leaves',        'sku' => 'FRV-0118', 'category' => 'fruits-vegetables', 'type' => 'Leafy Greens','unit' => '1 bundle',         'price' => 8.00,   'old_price' => null,   'stock' => 20, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Fresh kontomire (cocoyam leaves) for palaver sauce and other dishes.'],
            ['name' => 'Oranges (Sweet)',          'slug' => 'oranges-sweet',           'sku' => 'FRV-0129', 'category' => 'fruits-vegetables', 'type' => 'Fruits',     'unit' => '1kg (~5 pcs)',      'price' => 13.00,  'old_price' => null,   'stock' => 4,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Sweet, juicy Ghanaian oranges — perfect for juicing or snacking.'],
            ['name' => 'Cabbage',                 'slug' => 'cabbage',                 'sku' => 'FRV-0140', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '1 head',            'price' => 11.00,  'old_price' => null,   'stock' => 14, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Fresh green cabbage, great for salads, stir-fries and soups.'],
            ['name' => 'Mangoes (Kent)',           'slug' => 'mangoes-kent',            'sku' => 'FRV-0151', 'category' => 'fruits-vegetables', 'type' => 'Fruits',     'unit' => '3 pcs',             'price' => 20.00,  'old_price' => 24.00,  'stock' => 9,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Large, sweet Kent mangoes bursting with tropical flavour.'],
            ['name' => 'Green Plantain',          'slug' => 'green-plantain',          'sku' => 'FRV-0162', 'category' => 'fruits-vegetables', 'type' => 'Vegetables', 'unit' => '4 fingers',         'price' => 16.00,  'old_price' => null,   'stock' => 28, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Fresh green plantain for kelewele, chips or fufu.'],
            // Beverages
            ['name' => 'Voltic Still Water',      'slug' => 'voltic-still-water',      'sku' => 'BEV-0011', 'category' => 'beverages',         'type' => 'Water',      'unit' => '1.5L bottle',       'price' => 8.50,   'old_price' => null,   'stock' => 0,  'is_featured' => false, 'is_best_seller' => true,  'description' => 'Pure Voltic natural mineral water, still and refreshing.'],
            ['name' => 'Milo Chocolate Drink',    'slug' => 'milo-chocolate-drink',    'sku' => 'BEV-0022', 'category' => 'beverages',         'type' => 'Powdered',   'unit' => '500g tin',          'price' => 56.00,  'old_price' => 62.00,  'stock' => 12, 'is_featured' => true,  'is_best_seller' => true,  'description' => 'The beloved Milo chocolate malt drink — a Ghanaian household staple.'],
            // Dairy & Eggs
            ['name' => 'Cowbell Powdered Milk',   'slug' => 'cowbell-powdered-milk',   'sku' => 'DRY-0011', 'category' => 'dairy-eggs',        'type' => 'Milk',       'unit' => '400g tin',          'price' => 42.00,  'old_price' => 48.00,  'stock' => 3,  'is_featured' => true,  'is_best_seller' => true,  'description' => 'Full-cream Cowbell powdered milk, perfect for tea, coffee and cooking.'],
            // Pantry
            ['name' => 'Indomie Noodles (10 pk)', 'slug' => 'indomie-noodles-10-pk',   'sku' => 'PAN-0011', 'category' => 'pantry',            'type' => 'Noodles',    'unit' => '10 × 70g',          'price' => 28.00,  'old_price' => null,   'stock' => 30, 'is_featured' => true,  'is_best_seller' => true,  'description' => 'Indomie instant noodles 10-pack, the classic quick meal for the whole family.'],
            ['name' => 'Titus Sardines',           'slug' => 'titus-sardines',          'sku' => 'PAN-0022', 'category' => 'pantry',            'type' => 'Canned',     'unit' => '125g tin',          'price' => 12.00,  'old_price' => 14.00,  'stock' => 7,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Titus mackerel sardines in tomato sauce — a pantry essential.'],
            ['name' => 'Canola Cooking Oil',       'slug' => 'canola-cooking-oil',      'sku' => 'PAN-0033', 'category' => 'pantry',            'type' => 'Oils',       'unit' => '2L bottle',         'price' => 64.00,  'old_price' => 72.00,  'stock' => 9,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Heart-healthy canola cooking oil, light in taste and high smoke point.'],
            ['name' => 'Cowpea (Black-Eye Beans)', 'slug' => 'cowpea-black-eye-beans',  'sku' => 'PAN-0044', 'category' => 'pantry',            'type' => 'Legumes',    'unit' => '1kg bag',           'price' => 23.00,  'old_price' => 26.00,  'stock' => 0,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Dried black-eye cowpea beans, perfect for red-red and bean stews.'],
            ['name' => 'Akabanga Hot Sauce',       'slug' => 'akabanga-hot-sauce',      'sku' => 'PAN-0055', 'category' => 'pantry',            'type' => 'Sauces',     'unit' => '100ml',             'price' => 22.00,  'old_price' => null,   'stock' => 14, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Rwanda\'s famous Akabanga chilli oil — a tiny drop packs serious heat.'],
            // Personal Care
            ['name' => 'Dettol Soap 4-Pack',      'slug' => 'dettol-soap-4-pack',      'sku' => 'PRC-0011', 'category' => 'personal-care',     'type' => 'Soap',       'unit' => '4 × 75g bars',      'price' => 35.00,  'old_price' => 40.00,  'stock' => 18, 'is_featured' => true,  'is_best_seller' => true,  'description' => 'Dettol antibacterial soap 4-pack, trusted protection for the whole family.'],
            ['name' => 'Close-Up Toothpaste',     'slug' => 'close-up-toothpaste',     'sku' => 'PRC-0022', 'category' => 'personal-care',     'type' => 'Oral Care',  'unit' => '75ml tube',         'price' => 11.50,  'old_price' => null,   'stock' => 30, 'is_featured' => false, 'is_best_seller' => false, 'description' => 'Close-Up deep action toothpaste for fresh breath and white teeth.'],
            // Household
            ['name' => 'Sunlight Dish Liquid',    'slug' => 'sunlight-dish-liquid',    'sku' => 'HHD-0011', 'category' => 'household',         'type' => 'Cleaning',   'unit' => '750ml bottle',      'price' => 18.00,  'old_price' => null,   'stock' => 2,  'is_featured' => false, 'is_best_seller' => false, 'description' => 'Sunlight dish washing liquid, cuts grease fast and leaves dishes sparkling.'],
            ['name' => 'Omo Detergent',           'slug' => 'omo-detergent',           'sku' => 'HHD-0022', 'category' => 'household',         'type' => 'Laundry',    'unit' => '2kg bag',           'price' => 55.00,  'old_price' => 60.00,  'stock' => 11, 'is_featured' => false, 'is_best_seller' => true,  'description' => 'Omo auto washing powder, tough on stains and gentle on fabrics.'],
            // Snacks & Sweets
            ['name' => 'Golden Tree Chocolate',   'slug' => 'golden-tree-chocolate',   'sku' => 'SNK-0011', 'category' => 'snacks-sweets',     'type' => 'Chocolate',  'unit' => '100g bar',          'price' => 19.00,  'old_price' => null,   'stock' => 22, 'is_featured' => true,  'is_best_seller' => false, 'description' => 'Ghana\'s own Golden Tree dark chocolate — smooth, rich and locally made.'],
            // Baby Care
            ['name' => 'Pampers Baby Diapers',    'slug' => 'pampers-baby-diapers',    'sku' => 'BAB-0011', 'category' => 'baby-care',         'type' => 'Diapers',    'unit' => 'Size 4, 50 count',  'price' => 120.00, 'old_price' => 135.00, 'stock' => 5,  'is_featured' => true,  'is_best_seller' => false, 'description' => 'Pampers Active Baby diapers, extra dry protection for up to 12 hours.'],
        ];

        foreach ($products as &$p) {
            $p['image']      = null;
            $p['is_active']  = true;
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }
        unset($p);

        DB::table('products')->insert($products);
    }
}
