<?php

namespace App\Support;

class ProductImages
{
    private static array $map = [
        'cavendish-bananas'        => 'images/products/bananas.jpg',
        'green-plantain'           => 'images/products/bananas.jpg',
        'roma-tomatoes'            => 'images/products/tomatoes.jpg',
        'scotch-bonnet-peppers'    => 'images/products/tomatoes.jpg',
        'garden-eggs'              => 'images/products/tomatoes.jpg',
        'fresh-okra'               => 'images/products/tomatoes.jpg',
        'pineapple-sugarloaf'      => 'images/products/pineapple.jpg',
        'pawpaw-papaya'            => 'images/products/pineapple.jpg',
        'mangoes-kent'             => 'images/products/pineapple.jpg',
        'oranges-sweet'            => 'images/products/pineapple.jpg',
        'watermelon'               => 'images/products/pineapple.jpg',
        'avocado-local'            => 'images/products/pineapple.jpg',
        'cowbell-powdered-milk'    => 'images/products/milk.jpg',
        'voltic-still-water'       => 'images/products/water.jpg',
        'milo-chocolate-drink'     => 'images/products/milo.jpg',
        'indomie-noodles-10-pk'    => 'images/products/noodles.jpg',
        'indomie-noodles'          => 'images/products/noodles.jpg',
        'titus-sardines'           => 'images/products/sardines.jpg',
        'dettol-soap-4-pack'       => 'images/products/soap.jpg',
        'golden-tree-chocolate'    => 'images/products/chocolate.jpg',
        'canola-cooking-oil'       => 'images/products/cooking-oil.jpg',
        'sunlight-dish-liquid'     => 'images/products/dish-liquid.jpg',
        'red-onions'               => 'images/products/onions.jpg',
        'spring-onions'            => 'images/products/onions.jpg',
        'carrots'                  => 'images/products/carrots.jpg',
        'cabbage'                  => 'images/products/carrots.jpg',
        'cucumber'                 => 'images/products/carrots.jpg',
        'lettuce-iceberg'          => 'images/products/carrots.jpg',
        'kontomire-leaves'         => 'images/products/carrots.jpg',
        'omo-detergent'            => 'images/products/detergent.jpg',
        'close-up-toothpaste'      => 'images/products/toothpaste.jpg',
        'pampers-baby-diapers'     => 'images/products/diapers.jpg',
        'cowpea-black-eye-beans'   => 'images/products/beans.jpg',
        'akabanga-hot-sauce'       => 'images/products/hot-sauce.jpg',
        'ginger-root'              => 'images/products/ginger.jpg',
    ];

    public static function get(string $slug): string
    {
        return asset(self::$map[$slug] ?? 'images/products/bananas.jpg');
    }
}
