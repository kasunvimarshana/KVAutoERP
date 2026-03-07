<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Service A: Product model.
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price'       => fake()->randomFloat(2, 1, 999),
            'sku'         => strtoupper(fake()->bothify('??-####-??')),
        ];
    }
}
