<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductDescriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $descriptions = [
            // Fruits & Vegetables
            'cavendish-bananas'          => 'Sweet, ripe Cavendish bananas sourced fresh from farms in the Eastern Region. Perfect for eating fresh, smoothies, or baking.',
            'green-plantain'             => 'Firm, unripe plantains ideal for kelewele, chips, or boiled alongside your favourite stew. Sourced from local farms weekly.',
            'roma-tomatoes'              => 'Bright red Roma tomatoes with firm flesh and rich flavour — perfect for soups, stews, and sauces.',
            'scotch-bonnet-peppers'      => 'Fiery Scotch bonnet peppers that bring authentic heat to your Ghanaian dishes. Handle with care!',
            'garden-eggs'               => 'Fresh garden eggs with a mild, slightly bitter taste. A classic ingredient in garden egg stew and palaver sauce.',
            'fresh-okra'                => 'Tender young okra pods picked at peak freshness. Essential for light soup and okra stew.',
            'pineapple-sugarloaf'        => 'Ghana-grown Sugarloaf pineapples — exceptionally sweet with low acidity. Ready to eat, juice, or grill.',
            'pawpaw-papaya'             => 'Ripe, golden-fleshed pawpaw rich in vitamins A and C. Great fresh, blended, or in fruit salads.',
            'mangoes-kent'              => 'Juicy Kent mangoes at peak ripeness — fragrant, fiberless, and naturally sweet. A seasonal favourite.',
            'oranges-sweet'             => 'Freshly picked sweet oranges, perfect for juicing or eating. Packed with vitamin C.',
            'watermelon'               => 'Large, sweet watermelons with crisp red flesh. Refreshing, hydrating, and great for the whole family.',
            'avocado-local'             => 'Creamy local avocados with rich, buttery flesh. Great on toast, in salads, or blended into smoothies.',
            'red-onions-1kg'            => 'Firm, pungent red onions that are the backbone of Ghanaian cooking. Stored in a cool, dry environment for longevity.',
            'fresh-carrots-500g'        => 'Crisp, vibrant carrots with natural sweetness. Great raw as a snack, in stews, or stir-fried.',
            'sweet-potatoes-1kg'        => 'Naturally sweet and nutrient-rich local sweet potatoes. Boil, roast, or mash them for a hearty side.',
            'cabbage-head'              => 'Fresh, tightly packed cabbage head — great for coleslaw, stir-fries, and soups.',
            'cucumber'                  => 'Cool, crisp cucumbers perfect for salads, snacking, or infusing water. Refreshing and hydrating.',
            'spring-onions-bunch'       => 'Tender spring onions with a mild flavour. Use the green tops and white bulbs in stir-fries, soups, and garnishes.',
            'kontomire-cocoyam-leaves'  => 'Fresh cocoyam leaves (kontomire) for authentic palaver sauce and kontomire stew. Sourced from local farms.',

            // Beverages
            'voltic-still-water'        => 'Pure, naturally filtered Voltic mineral water. The most trusted bottled water brand in Ghana.',
            'milo-chocolate-drink'      => 'Nestlé Milo — the iconic chocolate and malt drink loved across Ghana. Great hot or cold, for kids and adults.',
            'coca-cola-can-330ml'       => 'Ice-cold Coca-Cola in a convenient 330ml can. The classic refreshing taste for any occasion.',
            'malta-guinness-330ml'      => 'A rich, non-alcoholic malt drink packed with B vitamins and energy. A popular favourite across Ghana.',
            'minute-maid-pulpy-orange-1l' => 'Real orange juice with natural pulp from Minute Maid. No added preservatives, full of vitamin C.',
            'alvaro-pineapple-330ml'    => 'Alvaro pineapple-flavoured malt drink — sweet, fruity, and refreshing. A Ghanaian party staple.',
            'lucozade-sport-orange-500ml' => 'Isotonic sports drink formulated to hydrate and replenish energy during physical activity.',
            'voltic-mineral-water-500ml' => 'Convenient 500ml Voltic mineral water — pure, crisp, and safe for the whole family.',

            // Dairy & Eggs
            'cowbell-powdered-milk'     => 'Cowbell full-cream powdered milk — smooth and creamy. Great in tea, porridge, and baking.',
            'farm-fresh-eggs-tray-of-30' => 'Fresh, free-range eggs from local farms — collected daily, washed, and packed in a sturdy tray of 30.',
            'fan-ice-yogurt-500ml'      => 'Fan Ice creamy yogurt in a generous 500ml cup. Chilled, smooth, and lightly sweetened.',
            'lactose-fresh-milk-1l'     => 'Fresh pasteurised whole milk — full of calcium and protein. Great for drinking, cereals, and cooking.',
            'blue-band-margarine-250g'  => 'Blue Band margarine — smooth, spreadable, and fortified with vitamins A and D. A breakfast classic.',

            // Pantry
            'indomie-noodles'           => 'Quick-cook Indomie instant noodles — a convenient and satisfying meal ready in 3 minutes. Season to taste.',
            'indomie-noodles-10-pk'     => 'Value pack of 10 Indomie instant noodle sachets — ideal for busy households and students.',
            'titus-sardines'            => 'Titus sardines in tomato sauce — a protein-rich pantry staple for rice, stew, or fried yam.',
            'canola-cooking-oil'        => 'Light, heart-healthy canola cooking oil with a neutral flavour. Ideal for frying, sautéing, and baking.',
            'cowpea-black-eye-beans'    => 'Dried black-eyed beans — protein-packed and versatile. Essential for red-red, bean stew, and bean cakes.',
            'gino-tomato-paste-400g'    => 'Rich, concentrated Gino tomato paste made from sun-ripened tomatoes. The base of countless Ghanaian dishes.',
            'golden-penny-sugar-1kg'    => 'Fine white granulated sugar from Golden Penny. Pure, consistent quality for beverages, baking, and cooking.',
            'kings-table-flour-2kg'     => 'Kings Table wheat flour — finely milled for soft bread, pancakes, pastries, and all your baking needs.',
            'uncle-sam-long-grain-rice-5kg' => 'Premium long-grain rice with a firm texture and pleasant aroma. Each grain cooks separately for perfect results.',
            'geisha-mackerel-in-tomato-200g' => 'Tender mackerel fillets in tangy tomato sauce. A quick protein addition to rice, yam, or plantain.',
            'nido-fortified-milk-powder-400g' => 'Nestlé NIDO fortified milk powder with vitamins and minerals. Nutritious and versatile for the whole family.',

            // Snacks & Sweets
            'golden-tree-chocolate'     => 'Smooth Ghanaian-made Golden Tree chocolate — a source of national pride and pure cocoa indulgence.',
            'mcvities-digestive-biscuits-400g' => 'Classic McVitie\'s Digestive biscuits with a satisfying crunch. Perfect with tea, coffee, or as a snack.',
            'pringles-original-165g'    => 'Stackable Pringles crisps in the original flavour — light, crunchy, and impossible to stop at just one.',
            'mentos-mint-roll'          => 'Fresh-tasting Mentos mint sweets — chewy, refreshing, and great for on-the-go freshness.',
            'kitkat-4-fingers-415g'     => 'KitKat four-finger chocolate wafer bar — the perfect break-time treat with crispy wafer and smooth chocolate.',

            // Personal Care
            'dettol-soap-4-pack'        => 'Dettol antibacterial soap that protects against 99.9% of germs. Dermatologically tested and gentle on skin.',
            'close-up-toothpaste'       => 'Close-Up toothpaste with active fluoride for cavity protection and lasting fresh breath.',
            'carotone-body-lotion-400ml' => 'Carotone moisturising body lotion with carrot oil — brightens skin tone while keeping it soft and nourished.',
            'pantene-pro-v-shampoo-400ml' => 'Pantene Pro-V shampoo that strengthens hair from root to tip, reducing breakage with every wash.',
            'always-ultra-thin-pads-8s' => 'Always Ultra Thin maxi pads with wings for all-day comfort and leak-free protection.',

            // Household
            'sunlight-dish-liquid'      => 'Sunlight washing-up liquid that cuts through grease fast, leaving dishes sparkling clean with a fresh lemon scent.',
            'omo-detergent'             => 'OMO active washing powder with deep-clean formula that removes tough stains even in cold water.',
            'vim-powder-cleanser-500g'  => 'Vim scouring powder that effortlessly removes grease, soap scum, and stains from sinks, pots, and surfaces.',
            'harpic-toilet-cleaner-500ml' => 'Harpic power plus toilet bowl cleaner that kills 99.9% of germs and removes limescale with ease.',

            // Baby Care
            'pampers-baby-diapers'      => 'Pampers Baby-Dry diapers with up to 12-hour dryness protection and a soft, breathable inner layer.',
            'cerelac-wheat-honey-400g'  => 'Nestlé CERELAC wheat and honey baby cereal — fortified with 18 nutrients for healthy growth from 6 months.',
            'huggies-pure-baby-wipes-56s' => 'Huggies Pure baby wipes made with 99% pure water — gentle enough for newborns, fragrance-free and alcohol-free.',
        ];

        $updated = 0;
        foreach ($descriptions as $slug => $desc) {
            $rows = Product::where('slug', $slug)
                ->where(fn ($q) => $q->whereNull('description')->orWhere('description', ''))
                ->update(['description' => $desc]);
            $updated += $rows;
        }

        $this->command->info("Descriptions applied to {$updated} products.");
    }
}
