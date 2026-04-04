<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Inventory\Domain\Entities\InventoryLevel;
use Modules\Inventory\Domain\RepositoryInterfaces\InventoryLevelRepositoryInterface;
use Modules\StockMovement\Application\Services\TransferStockService;
use Modules\StockMovement\Domain\Entities\StockMovement;
use Modules\StockMovement\Domain\RepositoryInterfaces\StockMovementRepositoryInterface;

class StockMovementTransferTest extends TestCase
{
    private function makeLevel(int $warehouseId, float $onHand = 100.0, float $reserved = 0.0): InventoryLevel
    {
        return new InventoryLevel(
            $warehouseId, 1, 1, $warehouseId, null,
            $onHand, $reserved, 0.0, 'fifo', null, null
        );
    }

    public function test_transfer_stock_validates_positive_quantity(): void
    {
        $movementRepo = $this->createMock(StockMovementRepositoryInterface::class);
        $levelRepo    = $this->createMock(InventoryLevelRepositoryInterface::class);

        $service = new TransferStockService($movementRepo, $levelRepo);

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(1, 1, 1, 2, 0.0, 10.0, 'TRF', 1);
    }

    public function test_transfer_stock_requires_different_warehouses(): void
    {
        $movementRepo = $this->createMock(StockMovementRepositoryInterface::class);
        $levelRepo    = $this->createMock(InventoryLevelRepositoryInterface::class);

        $service = new TransferStockService($movementRepo, $levelRepo);

        $this->expectException(\InvalidArgumentException::class);
        $service->execute(1, 1, 1, 1, 50.0, 10.0, 'TRF', 1); // same from/to warehouse
    }

    public function test_stock_movement_transfer_type_constant(): void
    {
        $this->assertEquals('transfer', StockMovement::TYPE_TRANSFER);
    }

    public function test_transfer_service_can_be_instantiated(): void
    {
        $movementRepo = $this->createMock(StockMovementRepositoryInterface::class);
        $levelRepo    = $this->createMock(InventoryLevelRepositoryInterface::class);

        $service = new TransferStockService($movementRepo, $levelRepo);
        $this->assertInstanceOf(TransferStockService::class, $service);
    }

    public function test_source_level_updated_after_transfer(): void
    {
        // Test the domain-level stock deduction directly (without DB transaction)
        $sourceLevel = $this->makeLevel(1, 100.0);
        $sourceLevel->issue(50.0);  // simulates what TransferStockService does internally

        $this->assertEquals(50.0, $sourceLevel->getQuantityOnHand());
    }

    public function test_destination_level_updated_after_transfer(): void
    {
        // Test the domain-level stock receipt directly
        $destLevel = $this->makeLevel(2, 0.0);
        $destLevel->receive(50.0);  // simulates what TransferStockService does internally

        $this->assertEquals(50.0, $destLevel->getQuantityOnHand());
    }

    public function test_transfer_insufficient_stock_domain_exception(): void
    {
        // Domain entity enforces insufficient stock rules
        $sourceLevel = $this->makeLevel(1, 30.0);

        $this->expectException(\DomainException::class);
        $sourceLevel->issue(50.0);  // only 30 available
    }
}
