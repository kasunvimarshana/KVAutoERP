<?php
namespace Database\Seeders;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = env('DEFAULT_TENANT_ID', '00000000-0000-0000-0000-000000000001');

        $electronics = Category::firstOrCreate(
            ['slug' => 'electronics', 'tenant_id' => $tenantId],
            ['name' => 'Electronics', 'status' => 'active']
        );

        Product::firstOrCreate(['code' => 'PROD-001', 'tenant_id' => $tenantId], [
            'category_id' => $electronics->id,
            'name'        => 'Laptop Pro 15',
            'sku'         => 'LP-PRO-15',
            'price'       => 1299.99,
            'cost'        => 900.00,
            'unit'        => 'unit',
            'status'      => 'active',
        ]);

        Product::firstOrCreate(['code' => 'PROD-002', 'tenant_id' => $tenantId], [
            'category_id' => $electronics->id,
            'name'        => 'Wireless Mouse',
            'sku'         => 'WM-001',
            'price'       => 29.99,
            'cost'        => 15.00,
            'unit'        => 'unit',
            'status'      => 'active',
        ]);
    }
}
