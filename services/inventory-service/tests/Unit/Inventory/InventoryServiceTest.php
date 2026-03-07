<?php

namespace Tests\Unit\Inventory;

use App\Modules\Inventory\DTOs\InventoryDTO;
use App\Modules\Inventory\Events\InventoryUpdated;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    private InventoryService $service;
    private InventoryRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(InventoryRepositoryInterface::class);
        $this->service    = new InventoryService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_inventory_successfully(): void
    {
        $dto = new InventoryDTO(
            productId:   1,
            productSku:  'SKU-001',
            quantity:    100,
            reorderLevel: 10,
            reorderQuantity: 50,
        );

        $inventory             = new Inventory();
        $inventory->id         = 1;
        $inventory->product_id = 1;
        $inventory->quantity   = 100;

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->repository->shouldReceive('findByProductId')->with(1)->once()->andReturn(null);
        $this->repository->shouldReceive('create')->with($dto)->once()->andReturn($inventory);

        $result = $this->service->createInventory($dto);

        $this->assertInstanceOf(Inventory::class, $result);
        $this->assertEquals(100, $result->quantity);
    }

    /** @test */
    public function it_throws_exception_when_inventory_already_exists(): void
    {
        $dto = new InventoryDTO(
            productId:   1,
            productSku:  'SKU-001',
            quantity:    100,
        );

        $existing             = new Inventory();
        $existing->id         = 5;
        $existing->product_id = 1;

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());
        $this->repository->shouldReceive('findByProductId')->with(1)->once()->andReturn($existing);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->createInventory($dto);
    }

    /** @test */
    public function it_dispatches_low_stock_alert_when_needed(): void
    {
        Event::fake();

        $inventory                     = new Inventory();
        $inventory->id                 = 1;
        $inventory->product_id         = 1;
        $inventory->product_sku        = 'SKU-001';
        $inventory->quantity           = 8;
        $inventory->reserved_quantity  = 0;
        $inventory->reorder_level      = 10;

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->repository->shouldReceive('findByProductId')->with(1)->once()->andReturn($inventory);
        $this->repository->shouldReceive('adjustQuantity')->with(1, -2)->once()->andReturn($inventory);

        $this->service->adjustQuantity(1, -2, 'sale');

        Event::assertDispatched(InventoryUpdated::class);
        Event::assertDispatched(LowStockAlert::class);
    }

    /** @test */
    public function it_throws_exception_when_insufficient_stock(): void
    {
        $inventory                    = new Inventory();
        $inventory->id                = 1;
        $inventory->product_id        = 1;
        $inventory->quantity          = 5;
        $inventory->reserved_quantity = 2;

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());
        $this->repository->shouldReceive('findByProductId')->with(1)->once()->andReturn($inventory);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->service->adjustQuantity(1, -10, 'sale');
    }
}
