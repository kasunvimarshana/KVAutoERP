<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Models\Category;
use App\Domain\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;

        $electronics = Category::create([
            'tenant_id' => $tenantId,
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
        ]);

        $laptops = Category::create([
            'tenant_id' => $tenantId,
            'parent_id' => $electronics->id,
            'name' => 'Laptops',
            'slug' => 'laptops',
            'is_active' => true,
        ]);

        $clothing = Category::create([
            'tenant_id' => $tenantId,
            'name' => 'Clothing',
            'slug' => 'clothing',
            'is_active' => true,
        ]);

        Product::create([
            'tenant_id' => $tenantId,
            'category_id' => $laptops->id,
            'name' => 'Dell XPS 15',
            'code' => 'DELL-XPS-15',
            'slug' => 'dell-xps-15',
            'price' => 1299.99,
            'cost_price' => 950.00,
            'sku' => 'DXPS15-001',
            'unit' => 'pcs',
            'is_active' => true,
            'attributes' => ['brand' => 'Dell', 'processor' => 'Intel Core i7', 'ram' => '16GB'],
            'tags' => ['laptop', 'dell', 'premium'],
        ]);

        Product::create([
            'tenant_id' => $tenantId,
            'category_id' => $clothing->id,
            'name' => 'Classic Blue T-Shirt',
            'code' => 'TSHIRT-BLUE-M',
            'slug' => 'classic-blue-tshirt',
            'price' => 29.99,
            'cost_price' => 12.00,
            'sku' => 'TSBL-M-001',
            'unit' => 'pcs',
            'is_active' => true,
            'attributes' => ['color' => 'blue', 'size' => 'M', 'material' => 'cotton'],
            'tags' => ['clothing', 'tshirt', 'casual'],
        ]);

        $this->command->info('Product database seeded!');
    }
}
