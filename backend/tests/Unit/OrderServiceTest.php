<?php

namespace Tests\Unit;

use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Order\Repositories\OrderRepositoryInterface;
use App\Modules\Order\Services\OrderService;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    private OrderService $service;
    private $orderRepoMock;
    private $productRepoMock;
    private $inventoryRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepoMock     = Mockery::mock(OrderRepositoryInterface::class);
        $this->productRepoMock   = Mockery::mock(ProductRepositoryInterface::class);
        $this->inventoryRepoMock = Mockery::mock(InventoryRepositoryInterface::class);

        $this->service = new OrderService(
            $this->orderRepoMock,
            $this->productRepoMock,
            $this->inventoryRepoMock,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_order_dispatches_event(): void
    {
        Event::fake();

        $product = new Product();
        $product->forceFill([
            'id'    => 'product-uuid',
            'sku'   => 'TEST-001',
            'name'  => 'Test Product',
            'price' => 25.00,
        ]);

        $order = new Order();
        $order->forceFill([
            'id'           => 'order-uuid',
            'tenant_id'    => 'tenant-uuid',
            'user_id'      => 'user-uuid',
            'order_number' => 'ORD-TEST-001',
            'status'       => 'pending',
            'subtotal'     => 25.00,
            'total'        => 25.00,
            'currency'     => 'USD',
        ]);

        $mockItems = new Collection([new OrderItem()]);
        $order->setRelation('items', $mockItems);

        $this->productRepoMock
            ->shouldReceive('findById')
            ->with('product-uuid', 'tenant-uuid')
            ->once()
            ->andReturn($product);

        $this->orderRepoMock
            ->shouldReceive('createWithItems')
            ->once()
            ->andReturn($order);

        $dto = OrderDTO::fromRequest([
            'tenant_id' => 'tenant-uuid',
            'user_id'   => 'user-uuid',
            'items'     => [
                [
                    'product_id' => 'product-uuid',
                    'quantity'   => 1,
                ],
            ],
        ]);

        $result = $this->service->create($dto);

        Event::assertDispatched(OrderCreated::class);
        $this->assertEquals('order-uuid', $result->id);
    }

    public function test_create_order_throws_when_product_not_found(): void
    {
        $this->productRepoMock
            ->shouldReceive('findById')
            ->with('invalid-product', 'tenant-uuid')
            ->once()
            ->andReturn(null);

        $dto = OrderDTO::fromRequest([
            'tenant_id' => 'tenant-uuid',
            'user_id'   => 'user-uuid',
            'items'     => [
                [
                    'product_id' => 'invalid-product',
                    'quantity'   => 1,
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product not found: invalid-product');

        $this->service->create($dto);
    }

    public function test_cancel_order_updates_status(): void
    {
        Event::fake();

        $order = new Order();
        $order->forceFill([
            'id'        => 'order-uuid',
            'tenant_id' => 'tenant-uuid',
            'status'    => 'pending',
        ]);

        $cancelledOrder = new Order();
        $cancelledOrder->forceFill([
            'id'           => 'order-uuid',
            'tenant_id'    => 'tenant-uuid',
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
        $cancelledOrder->setRelation('items', new Collection());

        $this->orderRepoMock
            ->shouldReceive('findById')
            ->with('order-uuid', 'tenant-uuid')
            ->once()
            ->andReturn($order);

        $this->orderRepoMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($cancelledOrder);

        $result = $this->service->cancel('order-uuid', 'tenant-uuid');

        $this->assertEquals('cancelled', $result->status);
    }
}
