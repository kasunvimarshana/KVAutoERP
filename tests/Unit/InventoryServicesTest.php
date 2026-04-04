<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Modules\Inventory\Application\Services\AddValuationLayerService;
use Modules\Inventory\Application\Services\AllocateStockService;
use Modules\Inventory\Application\Services\ConsumeValuationLayersService;
use Modules\Inventory\Application\Services\ReserveStockService;
use Modules\Inventory\Application\Services\ReleaseStockService;
use Modules\Inventory\Domain\Entities\InventoryBatch;
use Modules\Inventory\Domain\Entities\InventoryCycleCount;
use Modules\Inventory\Domain\Exceptions\InsufficientStockException;
use Modules\Inventory\Domain\Entities\InventoryLevel;
use Modules\Inventory\Domain\Entities\InventoryValuationLayer;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryBatchRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryLevelRepositoryInterface;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryValuationLayerRepositoryInterface;

class InventoryServicesTest extends TestCase
{
    // ──────────────────────────────────────────────────────────────────────
    // AddValuationLayerService
    // ──────────────────────────────────────────────────────────────────────

    private function makeLayer(float $qty = 100.0, float $cost = 5.0): InventoryValuationLayer
    {
        return new InventoryValuationLayer(1, 1, 1, 1, $qty, $qty, $cost, new \DateTimeImmutable(), 'REF-001', null, null, null);
    }

    public function test_add_valuation_layer_creates_layer(): void
    {
        /** @var InventoryValuationLayerRepositoryInterface&MockObject $layerRepo */
        $layerRepo = $this->createMock(InventoryValuationLayerRepositoryInterface::class);
        $expected  = $this->makeLayer(50.0, 10.0);

        $layerRepo->expects($this->once())
            ->method('create')
            ->willReturn($expected);

        $service = new AddValuationLayerService($layerRepo);
        $result  = $service->execute(1, 1, 1, 50.0, 10.0, 'GR-001');

        $this->assertEquals(50.0, $result->getQuantityRemaining());
        $this->assertEquals(10.0, $result->getUnitCost());
    }

    public function test_add_valuation_layer_rejects_zero_quantity(): void
    {
        $layerRepo = $this->createMock(InventoryValuationLayerRepositoryInterface::class);
        $service   = new AddValuationLayerService($layerRepo);

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(1, 1, 1, 0.0, 10.0, 'GR-001');
    }

    public function test_add_valuation_layer_rejects_negative_quantity(): void
    {
        $layerRepo = $this->createMock(InventoryValuationLayerRepositoryInterface::class);
        $service   = new AddValuationLayerService($layerRepo);

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(1, 1, 1, -5.0, 10.0, 'GR-001');
    }

    // ──────────────────────────────────────────────────────────────────────
    // ConsumeValuationLayersService
    // ──────────────────────────────────────────────────────────────────────

    public function test_consume_valuation_layers_fifo(): void
    {
        $layer1 = new InventoryValuationLayer(1, 1, 1, 1, 50.0, 50.0, 10.0, new \DateTimeImmutable('2024-01-01'), null, null, null, null);
        $layer2 = new InventoryValuationLayer(2, 1, 1, 1, 50.0, 50.0, 12.0, new \DateTimeImmutable('2024-01-02'), null, null, null, null);

        $layerRepo = $this->createMock(InventoryValuationLayerRepositoryInterface::class);
        $layerRepo->method('findLayersForConsumption')->willReturn([$layer1, $layer2]);
        $layerRepo->method('update')->willReturn(null);

        $service = new ConsumeValuationLayersService($layerRepo);
        // Consuming 75 units: 50 @ $10 + 25 @ $12 = $800 / 75 = $10.67
        $avgCost = $service->execute(1, 1, 1, 75.0, 'fifo');

        $this->assertEqualsWithDelta(10.666, $avgCost, 0.001);
        $this->assertEquals(0.0, $layer1->getQuantityRemaining());
        $this->assertEquals(25.0, $layer2->getQuantityRemaining());
    }

    public function test_consume_valuation_layers_rejects_zero_quantity(): void
    {
        $layerRepo = $this->createMock(InventoryValuationLayerRepositoryInterface::class);
        $service   = new ConsumeValuationLayersService($layerRepo);

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(1, 1, 1, 0.0, 'fifo');
    }

    public function test_consume_valuation_layers_throws_on_insufficient(): void
    {
        $layer = new InventoryValuationLayer(1, 1, 1, 1, 10.0, 10.0, 5.0, new \DateTimeImmutable(), null, null, null, null);

        $layerRepo = $this->createMock(InventoryValuationLayerRepositoryInterface::class);
        $layerRepo->method('findLayersForConsumption')->willReturn([$layer]);
        $layerRepo->method('update')->willReturn(null);

        $service = new ConsumeValuationLayersService($layerRepo);

        $this->expectException(\DomainException::class);
        $service->execute(1, 1, 1, 50.0, 'fifo'); // only 10 available
    }

    // ──────────────────────────────────────────────────────────────────────
    // AllocateStockService
    // ──────────────────────────────────────────────────────────────────────

    private function makeLevel(float $onHand = 100.0, float $reserved = 0.0): InventoryLevel
    {
        return new InventoryLevel(1, 1, 1, 1, null, $onHand, $reserved, 0.0, 'fifo', null, null);
    }

    private function makeBatch(int $id, float $remaining): InventoryBatch
    {
        return new InventoryBatch(
            $id, 1, 1, 1, "BATCH-{$id}", null, null,
            $remaining, $remaining, 10.0, null, null,
            new \DateTimeImmutable(), 'active', null, null, null,
        );
    }

    public function test_allocate_stock_returns_batch_allocations(): void
    {
        $level  = $this->makeLevel(100.0, 0.0);
        $batch1 = $this->makeBatch(1, 60.0);
        $batch2 = $this->makeBatch(2, 60.0);

        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn($level);

        $batchRepo = $this->createMock(InventoryBatchRepositoryInterface::class);
        $batchRepo->method('findActiveBatches')->willReturn([$batch1, $batch2]);

        $service     = new AllocateStockService($levelRepo, $batchRepo);
        $allocations = $service->execute(1, 1, 1, 80.0, 'fifo');

        $this->assertCount(2, $allocations);
        $this->assertEquals(60.0, $allocations[0]['quantity']);
        $this->assertEquals(20.0, $allocations[1]['quantity']);
    }

    public function test_allocate_stock_no_batches_allocates_from_level(): void
    {
        $level = $this->makeLevel(100.0, 0.0);

        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn($level);

        $batchRepo = $this->createMock(InventoryBatchRepositoryInterface::class);
        $batchRepo->method('findActiveBatches')->willReturn([]);

        $service     = new AllocateStockService($levelRepo, $batchRepo);
        $allocations = $service->execute(1, 1, 1, 50.0, 'fifo');

        $this->assertCount(1, $allocations);
        $this->assertNull($allocations[0]['batch_id']);
        $this->assertEquals(50.0, $allocations[0]['quantity']);
    }

    public function test_allocate_stock_throws_on_insufficient_stock(): void
    {
        $level = $this->makeLevel(30.0, 0.0);  // only 30 available

        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn($level);

        $batchRepo = $this->createMock(InventoryBatchRepositoryInterface::class);
        $batchRepo->method('findActiveBatches')->willReturn([]);

        $service = new AllocateStockService($levelRepo, $batchRepo);

        $this->expectException(\DomainException::class);
        $service->execute(1, 1, 1, 50.0, 'fifo');
    }

    public function test_allocate_stock_throws_on_zero_quantity(): void
    {
        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $batchRepo = $this->createMock(InventoryBatchRepositoryInterface::class);

        $service = new AllocateStockService($levelRepo, $batchRepo);

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(1, 1, 1, 0.0, 'fifo');
    }

    // ──────────────────────────────────────────────────────────────────────
    // ReserveStockService
    // ──────────────────────────────────────────────────────────────────────

    public function test_reserve_stock_service_reserves_successfully(): void
    {
        $level = $this->makeLevel(100.0, 0.0);

        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn($level);
        $levelRepo->expects($this->once())->method('update');

        $service = new ReserveStockService($levelRepo);
        $result  = $service->execute(1, 1, 1, 30.0);

        $this->assertEquals(30.0, $result->getQuantityReserved());
        $this->assertEquals(70.0, $result->getAvailableQuantity());
    }

    public function test_reserve_stock_service_throws_when_level_not_found(): void
    {
        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn(null);

        $service = new ReserveStockService($levelRepo);
        $this->expectException(InsufficientStockException::class);
        $service->execute(1, 1, 1, 10.0);
    }

    public function test_reserve_stock_service_throws_when_insufficient_available(): void
    {
        $level = $this->makeLevel(20.0, 15.0);  // available = 5

        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn($level);

        $service = new ReserveStockService($levelRepo);
        $this->expectException(\DomainException::class);
        $service->execute(1, 1, 1, 10.0);  // wants 10, only 5 available
    }

    // ──────────────────────────────────────────────────────────────────────
    // ReleaseStockService
    // ──────────────────────────────────────────────────────────────────────

    public function test_release_stock_service_releases_reservation(): void
    {
        $level = $this->makeLevel(100.0, 50.0);

        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn($level);
        $levelRepo->expects($this->once())->method('update');

        $service = new ReleaseStockService($levelRepo);
        $result  = $service->execute(1, 1, 1, 30.0);

        $this->assertEquals(20.0, $result->getQuantityReserved());
    }

    public function test_release_stock_service_clamps_reservation_to_zero(): void
    {
        $level = $this->makeLevel(100.0, 10.0);

        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn($level);
        $levelRepo->method('update');

        $service = new ReleaseStockService($levelRepo);
        $result  = $service->execute(1, 1, 1, 50.0);  // release more than reserved

        $this->assertEquals(0.0, $result->getQuantityReserved());
    }

    public function test_release_stock_service_throws_when_level_not_found(): void
    {
        $levelRepo = $this->createMock(InventoryLevelRepositoryInterface::class);
        $levelRepo->method('findByProduct')->willReturn(null);

        $service = new ReleaseStockService($levelRepo);
        $this->expectException(InsufficientStockException::class);
        $service->execute(1, 1, 1, 10.0);
    }

    // ──────────────────────────────────────────────────────────────────────
    // InventoryCycleCount entity
    // ──────────────────────────────────────────────────────────────────────

    private function makeCycleCount(string $status = 'pending'): InventoryCycleCount
    {
        return new InventoryCycleCount(
            1, 1, 1, null, $status, null, null, null, 'Annual count', null, null,
        );
    }

    public function test_cycle_count_creation(): void
    {
        $count = $this->makeCycleCount();
        $this->assertEquals(1, $count->getId());
        $this->assertEquals(1, $count->getTenantId());
        $this->assertEquals(1, $count->getWarehouseId());
        $this->assertNull($count->getProductId());
        $this->assertEquals('pending', $count->getStatus());
        $this->assertEquals('Annual count', $count->getNotes());
        $this->assertNull($count->getCountedBy());
    }

    public function test_cycle_count_start_transitions_to_in_progress(): void
    {
        $count = $this->makeCycleCount();
        $count->start(42);
        $this->assertEquals('in_progress', $count->getStatus());
        $this->assertEquals(42, $count->getCountedBy());
        $this->assertNotNull($count->getStartedAt());
    }

    public function test_cycle_count_start_fails_if_not_pending(): void
    {
        $count = $this->makeCycleCount('in_progress');
        $this->expectException(\DomainException::class);
        $count->start(42);
    }

    public function test_cycle_count_complete_transitions_from_in_progress(): void
    {
        $count = $this->makeCycleCount('in_progress');
        $count->complete();
        $this->assertEquals('completed', $count->getStatus());
        $this->assertNotNull($count->getCompletedAt());
    }

    public function test_cycle_count_complete_fails_if_pending(): void
    {
        $count = $this->makeCycleCount('pending');
        $this->expectException(\DomainException::class);
        $count->complete();
    }

    public function test_cycle_count_cancel_from_pending(): void
    {
        $count = $this->makeCycleCount('pending');
        $count->cancel();
        $this->assertEquals('cancelled', $count->getStatus());
    }

    public function test_cycle_count_cancel_from_in_progress(): void
    {
        $count = $this->makeCycleCount('in_progress');
        $count->cancel();
        $this->assertEquals('cancelled', $count->getStatus());
    }

    public function test_cycle_count_cancel_fails_if_already_completed(): void
    {
        $count = $this->makeCycleCount('completed');
        $this->expectException(\DomainException::class);
        $count->cancel();
    }

    public function test_cycle_count_full_lifecycle(): void
    {
        $count = $this->makeCycleCount();
        $count->start(7);
        $count->complete();

        $this->assertEquals('completed', $count->getStatus());
        $this->assertEquals(7, $count->getCountedBy());
        $this->assertNotNull($count->getStartedAt());
        $this->assertNotNull($count->getCompletedAt());
    }
}
