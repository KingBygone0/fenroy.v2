<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ── Fruits & Vegetables ──────────────────────────────────────────
            ['sku'=>'FRV-0131','name'=>'Red Onions 1kg',           'unit'=>'1 kg bag',    'price'=>12, 'old_price'=>15,  'stock'=>60,  'category'=>'fruits-vegetables','type'=>'Vegetables','is_featured'=>false,'is_best_seller'=>true],
            ['sku'=>'FRV-0142','name'=>'Fresh Carrots 500g',        'unit'=>'500 g pack',  'price'=>8,  'old_price'=>null,'stock'=>45,  'category'=>'fruits-vegetables','type'=>'Vegetables'],
            ['sku'=>'FRV-0153','name'=>'Sweet Potatoes 1kg',        'unit'=>'1 kg',        'price'=>10, 'old_price'=>null,'stock'=>40,  'category'=>'fruits-vegetables','type'=>'Vegetables'],
            ['sku'=>'FRV-0164','name'=>'Cabbage (Head)',             'unit'=>'1 head',      'price'=>9,  'old_price'=>null,'stock'=>30,  'category'=>'fruits-vegetables','type'=>'Vegetables'],
            ['sku'=>'FRV-0175','name'=>'Cucumber',                   'unit'=>'1 piece',     'price'=>4,  'old_price'=>null,'stock'=>55,  'category'=>'fruits-vegetables','type'=>'Vegetables'],
            ['sku'=>'FRV-0186','name'=>'Spring Onions (bunch)',      'unit'=>'1 bunch',     'price'=>5,  'old_price'=>null,'stock'=>35,  'category'=>'fruits-vegetables','type'=>'Vegetables'],
            ['sku'=>'FRV-0197','name'=>'Kontomire (Cocoyam Leaves)', 'unit'=>'1 bunch',     'price'=>6,  'old_price'=>null,'stock'=>25,  'category'=>'fruits-vegetables','type'=>'Vegetables'],

            // ── Beverages ───────────────────────────────────────────────────
            ['sku'=>'BEV-0033','name'=>'Coca-Cola Can 330ml',       'unit'=>'330 ml can',  'price'=>5,  'old_price'=>null,'stock'=>120, 'category'=>'beverages','type'=>'Soft Drinks','is_featured'=>true],
            ['sku'=>'BEV-0044','name'=>'Malta Guinness 330ml',      'unit'=>'330 ml bottle','price'=>6, 'old_price'=>null,'stock'=>96,  'category'=>'beverages','type'=>'Malt'],
            ['sku'=>'BEV-0055','name'=>'Minute Maid Pulpy Orange 1L','unit'=>'1 litre',    'price'=>14, 'old_price'=>18, 'stock'=>60,  'category'=>'beverages','type'=>'Juice','is_featured'=>true],
            ['sku'=>'BEV-0066','name'=>'Alvaro Pineapple 330ml',    'unit'=>'330 ml bottle','price'=>6, 'old_price'=>null,'stock'=>80,  'category'=>'beverages','type'=>'Malt'],
            ['sku'=>'BEV-0077','name'=>'Lucozade Sport Orange 500ml','unit'=>'500 ml bottle','price'=>8,'old_price'=>null,'stock'=>50,  'category'=>'beverages','type'=>'Sports Drinks'],
            ['sku'=>'BEV-0088','name'=>'Voltic Mineral Water 500ml','unit'=>'500 ml bottle','price'=>3, 'old_price'=>null,'stock'=>200, 'category'=>'beverages','type'=>'Water','is_best_seller'=>true],

            // ── Dairy & Eggs ────────────────────────────────────────────────
            ['sku'=>'DRY-0022','name'=>'Farm Fresh Eggs Tray of 30','unit'=>'30 eggs',     'price'=>35, 'old_price'=>40,  'stock'=>50,  'category'=>'dairy-eggs','type'=>'Eggs','is_featured'=>true,'is_best_seller'=>true],
            ['sku'=>'DRY-0033','name'=>'Fan Ice Yogurt 500ml',      'unit'=>'500 ml cup',  'price'=>12, 'old_price'=>null,'stock'=>40,  'category'=>'dairy-eggs','type'=>'Yogurt'],
            ['sku'=>'DRY-0044','name'=>'Lactose Fresh Milk 1L',     'unit'=>'1 litre',     'price'=>18, 'old_price'=>22,  'stock'=>35,  'category'=>'dairy-eggs','type'=>'Milk'],
            ['sku'=>'DRY-0055','name'=>'Blue Band Margarine 250g',  'unit'=>'250 g tub',   'price'=>16, 'old_price'=>null,'stock'=>45,  'category'=>'dairy-eggs','type'=>'Butter & Spreads'],

            // ── Pantry ──────────────────────────────────────────────────────
            ['sku'=>'PAN-0066','name'=>'Gino Tomato Paste 400g',    'unit'=>'400 g tin',   'price'=>9,  'old_price'=>11,  'stock'=>100, 'category'=>'pantry','type'=>'Canned Goods','is_best_seller'=>true],
            ['sku'=>'PAN-0077','name'=>'Golden Penny Sugar 1kg',    'unit'=>'1 kg pack',   'price'=>11, 'old_price'=>null,'stock'=>80,  'category'=>'pantry','type'=>'Staples'],
            ['sku'=>'PAN-0088','name'=>'Kings Table Flour 2kg',     'unit'=>'2 kg bag',    'price'=>18, 'old_price'=>22,  'stock'=>60,  'category'=>'pantry','type'=>'Staples'],
            ['sku'=>'PAN-0099','name'=>'Uncle Sam Long Grain Rice 5kg','unit'=>'5 kg bag', 'price'=>85, 'old_price'=>95,  'stock'=>40,  'category'=>'pantry','type'=>'Grains','is_featured'=>true,'is_best_seller'=>true],
            ['sku'=>'PAN-0110','name'=>'Geisha Mackerel in Tomato 200g','unit'=>'200 g tin','price'=>8, 'old_price'=>null,'stock'=>75,  'category'=>'pantry','type'=>'Canned Goods'],
            ['sku'=>'PAN-0121','name'=>'Nido Fortified Milk Powder 400g','unit'=>'400 g tin','price'=>38,'old_price'=>42, 'stock'=>35,  'category'=>'pantry','type'=>'Milk Powder'],

            // ── Snacks & Sweets ─────────────────────────────────────────────
            ['sku'=>'SNK-0022','name'=>'McVities Digestive Biscuits 400g','unit'=>'400 g pack','price'=>22,'old_price'=>26,'stock'=>55,  'category'=>'snacks-sweets','type'=>'Biscuits','is_featured'=>true],
            ['sku'=>'SNK-0033','name'=>'Pringles Original 165g',    'unit'=>'165 g tube',  'price'=>28, 'old_price'=>null,'stock'=>40,  'category'=>'snacks-sweets','type'=>'Chips'],
            ['sku'=>'SNK-0044','name'=>'Mentos Mint Roll',           'unit'=>'1 roll',      'price'=>4,  'old_price'=>null,'stock'=>100, 'category'=>'snacks-sweets','type'=>'Candy'],
            ['sku'=>'SNK-0055','name'=>'KitKat 4 Fingers 41.5g',    'unit'=>'41.5 g bar',  'price'=>8,  'old_price'=>null,'stock'=>80,  'category'=>'snacks-sweets','type'=>'Chocolate'],

            // ── Personal Care ────────────────────────────────────────────────
            ['sku'=>'PRC-0033','name'=>'Carotone Body Lotion 400ml','unit'=>'400 ml bottle','price'=>35,'old_price'=>42,  'stock'=>30,  'category'=>'personal-care','type'=>'Skin Care'],
            ['sku'=>'PRC-0044','name'=>'Pantene Pro-V Shampoo 400ml','unit'=>'400 ml bottle','price'=>38,'old_price'=>44, 'stock'=>25,  'category'=>'personal-care','type'=>'Hair Care'],
            ['sku'=>'PRC-0055','name'=>'Always Ultra Thin Pads 8s', 'unit'=>'8 pads',      'price'=>12, 'old_price'=>null,'stock'=>60,  'category'=>'personal-care','type'=>'Feminine Care'],

            // ── Household ────────────────────────────────────────────────────
            ['sku'=>'HHD-0033','name'=>'Vim Powder Cleanser 500g',  'unit'=>'500 g tin',   'price'=>9,  'old_price'=>null,'stock'=>70,  'category'=>'household','type'=>'Cleaning'],
            ['sku'=>'HHD-0044','name'=>'Harpic Toilet Cleaner 500ml','unit'=>'500 ml bottle','price'=>16,'old_price'=>20,  'stock'=>45,  'category'=>'household','type'=>'Cleaning'],

            // ── Baby Care ────────────────────────────────────────────────────
            ['sku'=>'BAB-0022','name'=>'Cerelac Wheat Honey 400g',  'unit'=>'400 g tin',   'price'=>55, 'old_price'=>62,  'stock'=>25,  'category'=>'baby-care','type'=>'Baby Food','is_featured'=>true],
            ['sku'=>'BAB-0033','name'=>'Huggies Pure Baby Wipes 56s','unit'=>'56 wipes',   'price'=>22, 'old_price'=>26,  'stock'=>40,  'category'=>'baby-care','type'=>'Wipes'],
        ];

        foreach ($products as $data) {
            if (Product::where('sku', $data['sku'])->exists()) {
                continue;
            }
            Product::create([
                'name'           => $data['name'],
                'slug'           => Str::slug($data['name']),
                'sku'            => $data['sku'],
                'unit'           => $data['unit'],
                'price'          => $data['price'],
                'old_price'      => $data['old_price'] ?? null,
                'stock'          => $data['stock'],
                'category'       => $data['category'],
                'type'           => $data['type'] ?? 'grocery',
                'description'    => '',
                'is_active'      => true,
                'is_featured'    => $data['is_featured'] ?? false,
                'is_best_seller' => $data['is_best_seller'] ?? false,
            ]);
        }

        $this->command->info('Demo products seeded: ' . count($products) . ' products processed.');
    }
}
