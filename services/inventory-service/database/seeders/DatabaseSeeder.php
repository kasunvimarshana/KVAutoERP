<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private const DEMO_TENANT = 'tenant-demo-001';

    public function run(): void
    {
        $catElectronics = Str::uuid()->toString();
        $catClothing    = Str::uuid()->toString();

        DB::table('categories')->insertOrIgnore([
            ['id' => $catElectronics, 'tenant_id' => self::DEMO_TENANT, 'name' => 'Electronics', 'slug' => 'electronics', 'parent_id' => null, 'description' => 'Electronic devices', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $catClothing,    'tenant_id' => self::DEMO_TENANT, 'name' => 'Clothing',    'slug' => 'clothing',    'parent_id' => null, 'description' => 'Apparel and accessories', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $products = [
            ['sku' => 'ELEC-LAPTOP-001', 'name' => 'Pro Laptop 15"',  'price' => 1299.99, 'cost_price' => 950.00,  'stock_quantity' => 50,  'min_stock_level' => 10, 'category_id' => $catElectronics],
            ['sku' => 'ELEC-PHONE-001',  'name' => 'Smartphone X12',  'price' => 799.99,  'cost_price' => 550.00,  'stock_quantity' => 120, 'min_stock_level' => 20, 'category_id' => $catElectronics],
            ['sku' => 'CLTH-SHIRT-001',  'name' => 'Classic T-Shirt', 'price' => 29.99,   'cost_price' => 8.00,    'stock_quantity' => 5,   'min_stock_level' => 10, 'category_id' => $catClothing],
            ['sku' => 'CLTH-JEANS-001',  'name' => 'Slim Fit Jeans',  'price' => 79.99,   'cost_price' => 30.00,   'stock_quantity' => 0,   'min_stock_level' => 5,  'category_id' => $catClothing],
        ];

        foreach ($products as $p) {
            DB::table('products')->insertOrIgnore([
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::DEMO_TENANT,
                'category_id'     => $p['category_id'],
                'sku'             => $p['sku'],
                'name'            => $p['name'],
                'description'     => 'Demo product: ' . $p['name'],
                'price'           => $p['price'],
                'cost_price'      => $p['cost_price'],
                'currency'        => 'USD',
                'stock_quantity'  => $p['stock_quantity'],
                'reserved_quantity' => 0,
                'min_stock_level' => $p['min_stock_level'],
                'max_stock_level' => $p['min_stock_level'] * 10,
                'unit'            => 'unit',
                'status'          => 'active',
                'is_active'       => true,
                'tags'            => '[]',
                'attributes'      => '{}',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
