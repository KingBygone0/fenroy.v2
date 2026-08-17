<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->unique()->words(3, true),
            'slug'           => fake()->unique()->slug(),
            'sku'            => strtoupper(fake()->unique()->bothify('SKU-####')),
            'description'    => fake()->optional()->sentence(),
            'unit'           => '1 unit',
            'image'          => null,
            'price'          => fake()->randomFloat(2, 1, 500),
            'old_price'      => null,
            'stock'          => fake()->numberBetween(0, 100),
            'category'       => 'pantry',
            'type'           => 'grocery',
            'is_active'      => true,
            'is_featured'    => false,
            'is_best_seller' => false,
        ];
    }
}
