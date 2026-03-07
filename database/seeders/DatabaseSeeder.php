<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with sample products (Service A)
     * and their corresponding inventory records (Service B).
     */
    public function run(): void
    {
        // Create 10 products via Service A
        $products = Product::factory(10)->create();

        // Create one inventory record per product via Service B
        $products->each(function (Product $product) {
            Inventory::factory()->create([
                'product_id'   => $product->id,
                'product_name' => $product->name,
            ]);
        });
    }
}

