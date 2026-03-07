<?php
namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    private const TENANT_1 = '11111111-1111-1111-1111-111111111111';
    private const TENANT_2 = '22222222-2222-2222-2222-222222222222';

    // Product IDs from product-service (known SKUs mapped to representative UUIDs)
    private const T1_PRODUCTS = [
        'drill'      => 'aaaaaaaa-0001-0001-0001-aaaaaaaaaaaa',
        'helmet'     => 'aaaaaaaa-0002-0002-0002-aaaaaaaaaaaa',
        'jack'       => 'aaaaaaaa-0003-0003-0003-aaaaaaaaaaaa',
        'gloves'     => 'aaaaaaaa-0004-0004-0004-aaaaaaaaaaaa',
        'multimeter' => 'aaaaaaaa-0005-0005-0005-aaaaaaaaaaaa',
    ];

    private const T2_PRODUCTS = [
        'chair'    => 'bbbbbbbb-0001-0001-0001-bbbbbbbbbbbb',
        'desk'     => 'bbbbbbbb-0002-0002-0002-bbbbbbbbbbbb',
        'hub'      => 'bbbbbbbb-0003-0003-0003-bbbbbbbbbbbb',
        'keyboard' => 'bbbbbbbb-0004-0004-0004-bbbbbbbbbbbb',
        'monitor'  => 'bbbbbbbb-0005-0005-0005-bbbbbbbbbbbb',
    ];

    public function run(): void
    {
        $inventoryData = [
            // Tenant 1
            ['tenant_id' => self::TENANT_1, 'product_id' => self::T1_PRODUCTS['drill'],      'warehouse_location' => 'Warehouse A - Shelf 1', 'quantity' => 50,  'reserved_quantity' => 5,  'unit' => 'set',   'min_level' => 10, 'max_level' => 100],
            ['tenant_id' => self::TENANT_1, 'product_id' => self::T1_PRODUCTS['helmet'],     'warehouse_location' => 'Warehouse A - Shelf 2', 'quantity' => 200, 'reserved_quantity' => 20, 'unit' => 'piece', 'min_level' => 30, 'max_level' => 500],
            ['tenant_id' => self::TENANT_1, 'product_id' => self::T1_PRODUCTS['jack'],       'warehouse_location' => 'Warehouse B - Floor 1', 'quantity' => 8,   'reserved_quantity' => 0,  'unit' => 'piece', 'min_level' => 5,  'max_level' => 20],
            ['tenant_id' => self::TENANT_1, 'product_id' => self::T1_PRODUCTS['gloves'],     'warehouse_location' => 'Warehouse A - Shelf 3', 'quantity' => 3,   'reserved_quantity' => 0,  'unit' => 'pair',  'min_level' => 20, 'max_level' => 200],
            ['tenant_id' => self::TENANT_1, 'product_id' => self::T1_PRODUCTS['multimeter'], 'warehouse_location' => 'Warehouse A - Shelf 4', 'quantity' => 25,  'reserved_quantity' => 2,  'unit' => 'piece', 'min_level' => 5,  'max_level' => 50],
            // Tenant 2
            ['tenant_id' => self::TENANT_2, 'product_id' => self::T2_PRODUCTS['chair'],    'warehouse_location' => 'Storage Room 1', 'quantity' => 15, 'reserved_quantity' => 2,  'unit' => 'piece', 'min_level' => 3,  'max_level' => 30],
            ['tenant_id' => self::TENANT_2, 'product_id' => self::T2_PRODUCTS['desk'],     'warehouse_location' => 'Storage Room 1', 'quantity' => 7,  'reserved_quantity' => 1,  'unit' => 'piece', 'min_level' => 2,  'max_level' => 20],
            ['tenant_id' => self::TENANT_2, 'product_id' => self::T2_PRODUCTS['hub'],      'warehouse_location' => 'Storage Room 2', 'quantity' => 60, 'reserved_quantity' => 10, 'unit' => 'piece', 'min_level' => 15, 'max_level' => 100],
            ['tenant_id' => self::TENANT_2, 'product_id' => self::T2_PRODUCTS['keyboard'], 'warehouse_location' => 'Storage Room 2', 'quantity' => 2,  'reserved_quantity' => 0,  'unit' => 'piece', 'min_level' => 10, 'max_level' => 50],
            ['tenant_id' => self::TENANT_2, 'product_id' => self::T2_PRODUCTS['monitor'],  'warehouse_location' => 'Storage Room 3', 'quantity' => 10, 'reserved_quantity' => 3,  'unit' => 'piece', 'min_level' => 3,  'max_level' => 25],
        ];

        foreach ($inventoryData as $data) {
            $inventoryId = Str::uuid()->toString();

            $inventory = Inventory::updateOrCreate(
                ['tenant_id' => $data['tenant_id'], 'product_id' => $data['product_id']],
                array_merge($data, ['id' => $inventoryId, 'status' => 'active'])
            );

            StockMovement::create([
                'id'            => Str::uuid()->toString(),
                'tenant_id'     => $data['tenant_id'],
                'inventory_id'  => $inventory->id,
                'product_id'    => $data['product_id'],
                'movement_type' => 'in',
                'quantity'      => $data['quantity'],
                'notes'         => 'Initial seeded stock',
            ]);
        }
    }
}
