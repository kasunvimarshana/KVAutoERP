<?php

namespace Tests\Feature;

use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Repositories\OrderRepositoryInterface;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantId = '550e8400-e29b-41d4-a716-446655440003';

    protected function setUp(): void
    {
        parent::setUp();
        app()->instance('tenant_id', $this->tenantId);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_order_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/orders', ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }

    public function test_create_order_validates_items(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\AuthenticateWithKeycloak::class)
             ->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);

        app()->instance('tenant_id', $this->tenantId);

        $response = $this->postJson('/api/v1/orders', [
            'user_id' => 'user-uuid',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)
                 ->assertJsonPath('errors.items.0', 'The items field is required.');
    }

    public function test_create_order_with_valid_data(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\AuthenticateWithKeycloak::class)
             ->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);

        app()->instance('tenant_id', $this->tenantId);

        $product = new Product();
        $product->forceFill([
            'id'    => '550e8400-e29b-41d4-a716-446655440099',
            'sku'   => 'ORDER-PROD-001',
            'name'  => 'Order Test Product',
            'price' => 50.00,
        ]);

        $order = new Order();
        $order->forceFill([
            'id'           => 'order-feat-uuid',
            'tenant_id'    => $this->tenantId,
            'user_id'      => '550e8400-e29b-41d4-a716-446655440050',
            'order_number' => 'ORD-TEST-001',
            'status'       => 'pending',
            'subtotal'     => 50.00,
            'tax'          => 0.00,
            'discount'     => 0.00,
            'total'        => 50.00,
            'currency'     => 'USD',
        ]);

        $item = new OrderItem();
        $item->forceFill([
            'product_id'   => '550e8400-e29b-41d4-a716-446655440099',
            'product_sku'  => 'ORDER-PROD-001',
            'product_name' => 'Order Test Product',
            'quantity'     => 1,
            'unit_price'   => 50.00,
            'discount'     => 0.00,
            'total'        => 50.00,
        ]);
        $order->setRelation('items', new Collection([$item]));

        $productRepo = Mockery::mock(ProductRepositoryInterface::class);
        $productRepo->shouldReceive('findById')
            ->with('550e8400-e29b-41d4-a716-446655440099', $this->tenantId)
            ->andReturn($product);
        app()->instance(ProductRepositoryInterface::class, $productRepo);

        $inventoryRepo = Mockery::mock(InventoryRepositoryInterface::class);
        $inventoryRepo->shouldReceive('findByProduct')->andReturn(null)->byDefault();
        app()->instance(InventoryRepositoryInterface::class, $inventoryRepo);

        $orderRepo = Mockery::mock(OrderRepositoryInterface::class);
        $orderRepo->shouldReceive('createWithItems')->andReturn($order);
        $orderRepo->shouldReceive('findById')->andReturn($order)->byDefault();
        $orderRepo->shouldReceive('update')->andReturn($order)->byDefault();
        app()->instance(OrderRepositoryInterface::class, $orderRepo);

        // Mock the Saga service to avoid full processing in tests
        $sagaService = Mockery::mock(\App\Modules\Order\Services\OrderSagaService::class);
        $sagaService->shouldReceive('processOrder')->andReturn(true);
        app()->instance(\App\Modules\Order\Services\OrderSagaService::class, $sagaService);

        $response = $this->postJson('/api/v1/orders', [
            'user_id' => '550e8400-e29b-41d4-a716-446655440050',
            'items'   => [
                [
                    'product_id' => '550e8400-e29b-41d4-a716-446655440099',
                    'quantity'   => 1,
                ],
            ],
        ], ['Accept' => 'application/json']);

        $response->assertStatus(201)
                 ->assertJsonPath('data.order_number', 'ORD-TEST-001');
    }
}
