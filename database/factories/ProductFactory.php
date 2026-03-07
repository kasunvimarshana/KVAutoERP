<?php

namespace Database\Factories;

use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price'       => $this->faker->randomFloat(2, 1, 1000),
            'stock'       => $this->faker->numberBetween(0, 500),
            'sku'         => strtoupper($this->faker->unique()->bothify('SKU-####-??')),
            'is_active'   => true,
        ];
    }
}
