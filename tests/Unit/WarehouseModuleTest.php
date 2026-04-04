<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;

class WarehouseModuleTest extends TestCase
{
    private function makeWarehouse(array $overrides = []): Warehouse
    {
        return new Warehouse(
            $overrides['id'] ?? 1, $overrides['tenant_id'] ?? 1,
            $overrides['name'] ?? 'Main WH', $overrides['code'] ?? 'WH-001',
            $overrides['type'] ?? 'standard', $overrides['address'] ?? '123 Main St',
            $overrides['is_active'] ?? true, null, null, null, null
        );
    }
    private function makeLocation(array $overrides = []): WarehouseLocation
    {
        return new WarehouseLocation(
            $overrides['id'] ?? 1, $overrides['tenant_id'] ?? 1,
            $overrides['warehouse_id'] ?? 1, null, 'Aisle A', 'A-001', 'aisle', 0, true, null, null
        );
    }

    public function test_warehouse_creation(): void
    {
        $wh = $this->makeWarehouse();
        $this->assertEquals(1, $wh->getId());
        $this->assertEquals('WH-001', $wh->getCode());
        $this->assertTrue($wh->isActive());
    }

    public function test_warehouse_activate_deactivate(): void
    {
        $wh = $this->makeWarehouse(['is_active' => false]);
        $this->assertFalse($wh->isActive());
        $wh->activate();
        $this->assertTrue($wh->isActive());
        $wh->deactivate();
        $this->assertFalse($wh->isActive());
    }

    public function test_warehouse_location_creation(): void
    {
        $loc = $this->makeLocation();
        $this->assertEquals(1, $loc->getWarehouseId());
        $this->assertEquals('A-001', $loc->getCode());
        $this->assertEquals('aisle', $loc->getType());
        $this->assertTrue($loc->isActive());
    }
}
