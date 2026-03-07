<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Service B: Inventory model.
 */
class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id'         => Product::factory(),
            'product_name'       => fake()->words(3, true),
            'quantity'           => fake()->numberBetween(0, 500),
            'warehouse_location' => fake()->city() . ' Warehouse',
            'status'             => fake()->randomElement(['in_stock', 'low_stock', 'out_of_stock']),
        ];
    }
}
