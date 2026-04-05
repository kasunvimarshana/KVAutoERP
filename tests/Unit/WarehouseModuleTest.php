<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Warehouse\Domain\Entities\Warehouse;
use Modules\Warehouse\Domain\Entities\WarehouseLocation;
use PHPUnit\Framework\TestCase;
class WarehouseModuleTest extends TestCase {
    public function test_warehouse_entity(): void {
        $w = new Warehouse(1, 1, 'Main Warehouse', 'WH001', 'standard', '123 Main St', true, true);
        $this->assertSame('WH001', $w->getCode());
        $this->assertTrue($w->isDefault());
        $this->assertSame('standard', $w->getType());
    }
    public function test_warehouse_location_entity(): void {
        $loc = new WarehouseLocation(1, 1, 'Aisle A', 'A', 'aisle', null, '/1/', 0, true, true, true);
        $this->assertSame('aisle', $loc->getType());
        $this->assertTrue($loc->isPickable());
        $this->assertTrue($loc->isReceivable());
    }
    public function test_warehouse_bin_location(): void {
        $bin = new WarehouseLocation(5, 1, 'Bin A-01-01', 'A-01-01', 'bin', 4, '/1/2/3/4/5/', 4, true, true, false);
        $this->assertSame(4, $bin->getLevel());
        $this->assertSame('/1/2/3/4/5/', $bin->getPath());
        $this->assertFalse($bin->isReceivable());
    }
}
