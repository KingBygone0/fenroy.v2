<?php

namespace App\Support;

class ProductImages
{
    private static array $map = [
        // Fruits & Vegetables
        'cavendish-bananas'                => 'images/products/bananas.jpg',
        'green-plantain'                   => 'images/products/bananas.jpg',
        'roma-tomatoes'                    => 'images/products/tomatoes.jpg',
        'scotch-bonnet-peppers'            => 'images/products/tomatoes.jpg',
        'garden-eggs'                      => 'images/products/tomatoes.jpg',
        'fresh-okra'                       => 'images/products/tomatoes.jpg',
        'pineapple-sugarloaf'              => 'images/products/pineapple.jpg',
        'pawpaw-papaya'                    => 'images/products/pineapple.jpg',
        'mangoes-kent'                     => 'images/products/pineapple.jpg',
        'oranges-sweet'                    => 'images/products/pineapple.jpg',
        'watermelon'                       => 'images/products/pineapple.jpg',
        'avocado-local'                    => 'images/products/pineapple.jpg',
        'red-onions-1kg'                   => 'images/products/onions.jpg',
        'fresh-carrots-500g'               => 'images/products/carrots.jpg',
        'sweet-potatoes-1kg'               => 'images/products/carrots.jpg',
        'cabbage-head'                     => 'images/products/carrots.jpg',
        'cucumber'                         => 'images/products/carrots.jpg',
        'spring-onions-bunch'              => 'images/products/onions.jpg',
        'kontomire-cocoyam-leaves'         => 'images/products/carrots.jpg',
        'red-onions'                       => 'images/products/onions.jpg',
        'spring-onions'                    => 'images/products/onions.jpg',
        'carrots'                          => 'images/products/carrots.jpg',
        'cabbage'                          => 'images/products/carrots.jpg',
        'lettuce-iceberg'                  => 'images/products/carrots.jpg',
        'kontomire-leaves'                 => 'images/products/carrots.jpg',

        // Beverages
        'voltic-still-water'               => 'images/products/water.jpg',
        'milo-chocolate-drink'             => 'images/products/milo.jpg',
        'coca-cola-can-330ml'              => 'images/products/juice.jpg',
        'malta-guinness-330ml'             => 'images/products/milo.jpg',
        'minute-maid-pulpy-orange-1l'      => 'images/products/juice.jpg',
        'alvaro-pineapple-330ml'           => 'images/products/juice.jpg',
        'lucozade-sport-orange-500ml'      => 'images/products/juice.jpg',
        'voltic-mineral-water-500ml'       => 'images/products/water.jpg',

        // Dairy & Eggs
        'cowbell-powdered-milk'            => 'images/products/milk.jpg',
        'farm-fresh-eggs-tray-of-30'       => 'images/products/eggs.jpg',
        'fan-ice-yogurt-500ml'             => 'images/products/yogurt.jpg',
        'lactose-fresh-milk-1l'            => 'images/products/milk.jpg',
        'blue-band-margarine-250g'         => 'images/products/butter.jpg',

        // Pantry
        'indomie-noodles-10-pk'            => 'images/products/noodles.jpg',
        'indomie-noodles'                  => 'images/products/noodles.jpg',
        'titus-sardines'                   => 'images/products/sardines.jpg',
        'canola-cooking-oil'               => 'images/products/cooking-oil.jpg',
        'cowpea-black-eye-beans'           => 'images/products/beans.jpg',
        'akabanga-hot-sauce'               => 'images/products/hot-sauce.jpg',
        'ginger-root'                      => 'images/products/ginger.jpg',
        'gino-tomato-paste-400g'           => 'images/products/tomato-paste.jpg',
        'golden-penny-sugar-1kg'           => 'images/products/sugar.jpg',
        'kings-table-flour-2kg'            => 'images/products/flour.jpg',
        'uncle-sam-long-grain-rice-5kg'    => 'images/products/rice.jpg',
        'geisha-mackerel-in-tomato-200g'   => 'images/products/sardines.jpg',
        'nido-fortified-milk-powder-400g'  => 'images/products/milk.jpg',

        // Snacks & Sweets
        'golden-tree-chocolate'            => 'images/products/chocolate.jpg',
        'mcvities-digestive-biscuits-400g' => 'images/products/biscuits.jpg',
        'pringles-original-165g'           => 'images/products/chips.jpg',
        'mentos-mint-roll'                 => 'images/products/chocolate.jpg',
        'kitkat-4-fingers-415g'            => 'images/products/chocolate.jpg',

        // Personal Care
        'dettol-soap-4-pack'               => 'images/products/soap.jpg',
        'close-up-toothpaste'              => 'images/products/toothpaste.jpg',
        'carotone-body-lotion-400ml'       => 'images/products/lotion.jpg',
        'pantene-pro-v-shampoo-400ml'      => 'images/products/shampoo.jpg',
        'always-ultra-thin-pads-8s'        => 'images/products/soap.jpg',

        // Household
        'sunlight-dish-liquid'             => 'images/products/dish-liquid.jpg',
        'omo-detergent'                    => 'images/products/detergent.jpg',
        'vim-powder-cleanser-500g'         => 'images/products/detergent.jpg',
        'harpic-toilet-cleaner-500ml'      => 'images/products/dish-liquid.jpg',

        // Baby Care
        'pampers-baby-diapers'             => 'images/products/diapers.jpg',
        'cerelac-wheat-honey-400g'         => 'images/products/baby-food.jpg',
        'huggies-pure-baby-wipes-56s'      => 'images/products/diapers.jpg',
    ];

    public static function get(string $slug): string
    {
        return asset(self::$map[$slug] ?? 'images/products/tomatoes.jpg');
    }
}
