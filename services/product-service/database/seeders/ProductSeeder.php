<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private const TENANT_1 = '11111111-1111-1111-1111-111111111111';
    private const TENANT_2 = '22222222-2222-2222-2222-222222222222';

    public function run(): void
    {
        $products = [
            // Tenant 1
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_1,
                'name'            => 'Industrial Drill Bit Set',
                'sku'             => 'T1-DRILL-001',
                'description'     => 'Professional grade drill bit set for industrial use.',
                'category'        => 'Tools',
                'price'           => '149.99',
                'cost'            => '75.00',
                'stock_quantity'  => 50,
                'min_stock_level' => 10,
                'unit'            => 'set',
                'status'          => 'active',
                'metadata'        => json_encode(['brand' => 'ProTools', 'warranty' => '2 years']),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_1,
                'name'            => 'Safety Helmet',
                'sku'             => 'T1-SAFETY-001',
                'description'     => 'Hard hat meeting ANSI Z89.1 standard.',
                'category'        => 'Safety',
                'price'           => '29.99',
                'cost'            => '12.00',
                'stock_quantity'  => 200,
                'min_stock_level' => 30,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['color' => 'yellow', 'size' => 'universal']),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_1,
                'name'            => 'Hydraulic Floor Jack',
                'sku'             => 'T1-JACK-001',
                'description'     => '3-ton hydraulic floor jack with safety valve.',
                'category'        => 'Equipment',
                'price'           => '299.00',
                'cost'            => '145.00',
                'stock_quantity'  => 8,
                'min_stock_level' => 5,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['capacity' => '3 ton', 'lift_height' => '18 inch']),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_1,
                'name'            => 'Welding Gloves',
                'sku'             => 'T1-GLOVE-001',
                'description'     => 'Heat resistant leather welding gloves.',
                'category'        => 'Safety',
                'price'           => '19.99',
                'cost'            => '8.50',
                'stock_quantity'  => 3,
                'min_stock_level' => 20,
                'unit'            => 'pair',
                'status'          => 'active',
                'metadata'        => json_encode(['material' => 'cowhide leather']),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_1,
                'name'            => 'Digital Multimeter',
                'sku'             => 'T1-METER-001',
                'description'     => 'Auto-ranging digital multimeter with backlit display.',
                'category'        => 'Electronics',
                'price'           => '89.99',
                'cost'            => '42.00',
                'stock_quantity'  => 25,
                'min_stock_level' => 5,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['accuracy' => '0.5%', 'display' => '4000 count']),
            ],
            // Tenant 2
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_2,
                'name'            => 'Office Chair Ergonomic',
                'sku'             => 'T2-CHAIR-001',
                'description'     => 'Lumbar support ergonomic office chair.',
                'category'        => 'Furniture',
                'price'           => '399.00',
                'cost'            => '180.00',
                'stock_quantity'  => 15,
                'min_stock_level' => 3,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['max_weight' => '150kg', 'adjustable' => true]),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_2,
                'name'            => 'Standing Desk',
                'sku'             => 'T2-DESK-001',
                'description'     => 'Motorized standing desk with memory presets.',
                'category'        => 'Furniture',
                'price'           => '799.00',
                'cost'            => '380.00',
                'stock_quantity'  => 7,
                'min_stock_level' => 2,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['size' => '160x80cm', 'presets' => 4]),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_2,
                'name'            => 'USB-C Hub 7-in-1',
                'sku'             => 'T2-HUB-001',
                'description'     => '7-port USB-C hub with HDMI, USB-A, SD card reader.',
                'category'        => 'Electronics',
                'price'           => '49.99',
                'cost'            => '18.00',
                'stock_quantity'  => 60,
                'min_stock_level' => 15,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['ports' => 7, 'max_power' => '100W']),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_2,
                'name'            => 'Wireless Keyboard',
                'sku'             => 'T2-KB-001',
                'description'     => 'Slim wireless keyboard with 2.4GHz receiver.',
                'category'        => 'Electronics',
                'price'           => '59.99',
                'cost'            => '25.00',
                'stock_quantity'  => 2,
                'min_stock_level' => 10,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['layout' => 'QWERTY', 'battery' => 'AA x2']),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => self::TENANT_2,
                'name'            => 'Monitor 27" 4K',
                'sku'             => 'T2-MON-001',
                'description'     => '27-inch 4K IPS monitor with HDR400.',
                'category'        => 'Electronics',
                'price'           => '549.00',
                'cost'            => '260.00',
                'stock_quantity'  => 10,
                'min_stock_level' => 3,
                'unit'            => 'piece',
                'status'          => 'active',
                'metadata'        => json_encode(['resolution' => '3840x2160', 'refresh_rate' => '60Hz']),
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['tenant_id' => $product['tenant_id'], 'sku' => $product['sku']],
                $product
            );
        }
    }
}
