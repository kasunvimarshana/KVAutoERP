<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Modules\Inventory\Application\Services\AddValuationLayerService;
use Modules\Inventory\Application\Services\AllocateStockService;
use Modules\Inventory\Application\Services\ConsumeValuationLayersService;
use Modules\Inventory\Domain\Entities\InventoryBatch;
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
}
