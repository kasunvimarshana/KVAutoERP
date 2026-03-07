<?php

namespace Tests\Unit;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Events\InventoryUpdated;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    private InventoryService $service;
    private $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(InventoryRepositoryInterface::class);
        $this->service        = new InventoryService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_inventory_dispatches_event(): void
    {
        Event::fake();

        $inventory = new Inventory();
        $inventory->forceFill([
            'id'         => 'inv-uuid',
            'tenant_id'  => 'tenant-uuid',
            'product_id' => 'product-uuid',
            'quantity'   => 100,
            'status'     => 'in_stock',
        ]);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($inventory);

        $dto = new InventoryDTO(
            tenantId:  'tenant-uuid',
            productId: 'product-uuid',
            quantity:  100,
        );

        $result = $this->service->create($dto);

        Event::assertDispatched(InventoryUpdated::class, function ($event) {
            return $event->action === 'created';
        });

        $this->assertEquals(100, $result->quantity);
    }

    public function test_adjust_stock_dispatches_event(): void
    {
        Event::fake();

        $inventory = new Inventory();
        $inventory->forceFill([
            'id'        => 'inv-uuid',
            'tenant_id' => 'tenant-uuid',
            'quantity'  => 100,
            'status'    => 'in_stock',
        ]);

        $adjustedInventory = new Inventory();
        $adjustedInventory->forceFill([
            'id'        => 'inv-uuid',
            'tenant_id' => 'tenant-uuid',
            'quantity'  => 90,
            'status'    => 'in_stock',
        ]);

        $this->repositoryMock
            ->shouldReceive('findById')
            ->with('inv-uuid', 'tenant-uuid')
            ->once()
            ->andReturn($inventory);

        $this->repositoryMock
            ->shouldReceive('adjustQuantity')
            ->with($inventory, -10)
            ->once()
            ->andReturn($adjustedInventory);

        $result = $this->service->adjustStock('inv-uuid', 'tenant-uuid', -10, 'Sale');

        Event::assertDispatched(InventoryUpdated::class, function ($event) {
            return $event->action === 'adjusted';
        });

        $this->assertEquals(90, $result->quantity);
    }

    public function test_reserve_stock_returns_false_on_insufficient_stock(): void
    {
        $inventory = new Inventory();
        $inventory->forceFill([
            'id'                => 'inv-uuid',
            'tenant_id'         => 'tenant-uuid',
            'quantity'          => 5,
            'reserved_quantity' => 0,
        ]);

        $this->repositoryMock
            ->shouldReceive('findById')
            ->with('inv-uuid', 'tenant-uuid')
            ->once()
            ->andReturn($inventory);

        $this->repositoryMock
            ->shouldReceive('reserveQuantity')
            ->with($inventory, 100)
            ->once()
            ->andReturn(false);

        $result = $this->service->reserveStock('inv-uuid', 'tenant-uuid', 100);

        $this->assertFalse($result);
    }
}
