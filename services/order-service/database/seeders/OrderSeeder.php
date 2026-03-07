<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    private const TENANT_1 = '11111111-1111-1111-1111-111111111111';
    private const TENANT_2 = '22222222-2222-2222-2222-222222222222';
    private const USER_T1  = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    private const USER_T2  = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';

    public function run(): void
    {
        $orders = [
            ['tenant_id' => self::TENANT_1, 'user_id' => self::USER_T1, 'status' => 'confirmed', 'currency' => 'USD', 'subtotal' => '299.98', 'tax' => '30.00', 'discount' => '0.00', 'total' => '329.98', 'shipping_address' => json_encode(['line1' => '123 Main St', 'city' => 'New York', 'state' => 'NY', 'country' => 'US', 'zip' => '10001']), 'billing_address' => null, 'notes' => null, 'metadata' => null,
                'items' => [
                    ['product_id' => Str::uuid(), 'product_name' => 'Industrial Drill Bit Set', 'sku' => 'T1-DRILL-001', 'quantity' => 2, 'unit_price' => '149.99', 'total_price' => '299.98'],
                ],
            ],
            ['tenant_id' => self::TENANT_1, 'user_id' => self::USER_T1, 'status' => 'pending', 'currency' => 'USD', 'subtotal' => '59.97', 'tax' => '6.00', 'discount' => '5.00', 'total' => '60.97', 'shipping_address' => json_encode(['line1' => '456 Oak Ave', 'city' => 'Boston', 'state' => 'MA', 'country' => 'US', 'zip' => '02101']), 'billing_address' => null, 'notes' => 'Urgent delivery', 'metadata' => null,
                'items' => [
                    ['product_id' => Str::uuid(), 'product_name' => 'Safety Helmet', 'sku' => 'T1-SAFETY-001', 'quantity' => 3, 'unit_price' => '19.99', 'total_price' => '59.97'],
                ],
            ],
            ['tenant_id' => self::TENANT_2, 'user_id' => self::USER_T2, 'status' => 'delivered', 'currency' => 'USD', 'subtotal' => '1198.00', 'tax' => '120.00', 'discount' => '50.00', 'total' => '1268.00', 'shipping_address' => json_encode(['line1' => '789 Pine Rd', 'city' => 'Seattle', 'state' => 'WA', 'country' => 'US', 'zip' => '98101']), 'billing_address' => null, 'notes' => null, 'metadata' => null,
                'items' => [
                    ['product_id' => Str::uuid(), 'product_name' => 'Standing Desk', 'sku' => 'T2-DESK-001', 'quantity' => 1, 'unit_price' => '799.00', 'total_price' => '799.00'],
                    ['product_id' => Str::uuid(), 'product_name' => 'Office Chair Ergonomic', 'sku' => 'T2-CHAIR-001', 'quantity' => 1, 'unit_price' => '399.00', 'total_price' => '399.00'],
                ],
            ],
            ['tenant_id' => self::TENANT_2, 'user_id' => self::USER_T2, 'status' => 'cancelled', 'currency' => 'USD', 'subtotal' => '49.99', 'tax' => '5.00', 'discount' => '0.00', 'total' => '54.99', 'shipping_address' => json_encode(['line1' => '321 Elm St', 'city' => 'Portland', 'state' => 'OR', 'country' => 'US', 'zip' => '97201']), 'billing_address' => null, 'notes' => 'Customer requested cancellation', 'metadata' => null,
                'items' => [
                    ['product_id' => Str::uuid(), 'product_name' => 'USB-C Hub 7-in-1', 'sku' => 'T2-HUB-001', 'quantity' => 1, 'unit_price' => '49.99', 'total_price' => '49.99'],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $items = $orderData['items'];
            unset($orderData['items']);

            $order = Order::create(array_merge($orderData, ['id' => Str::uuid()->toString()]));

            foreach ($items as $item) {
                OrderItem::create(array_merge($item, ['id' => Str::uuid()->toString(), 'order_id' => $order->id]));
            }
        }
    }
}
