<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Inventory\Domain\ValueObjects\ValuationMethod;
use Modules\Inventory\Domain\ValueObjects\ManagementMethod;
use Modules\Inventory\Domain\ValueObjects\StockRotationStrategy;
use Modules\Inventory\Domain\ValueObjects\AllocationAlgorithm;
use Modules\Inventory\Domain\ValueObjects\CycleCountMethod;
use Modules\Inventory\Domain\Entities\InventoryLevel;

class InventoryModuleTest extends TestCase
{
    // --------------- ValuationMethod VO ---------------

    public function test_valuation_method_fifo(): void
    {
        $vo = ValuationMethod::from(ValuationMethod::FIFO);
        $this->assertSame('fifo', (string) $vo);
    }

    public function test_valuation_method_lifo(): void
    {
        $vo = ValuationMethod::from(ValuationMethod::LIFO);
        $this->assertSame('lifo', (string) $vo);
    }

    public function test_valuation_method_average(): void
    {
        $vo = ValuationMethod::from(ValuationMethod::AVERAGE);
        $this->assertSame('average', (string) $vo);
    }

    public function test_valuation_method_specific(): void
    {
        $vo = ValuationMethod::from(ValuationMethod::SPECIFIC);
        $this->assertSame('specific', (string) $vo);
    }

    public function test_valuation_method_standard(): void
    {
        $vo = ValuationMethod::from(ValuationMethod::STANDARD);
        $this->assertSame('standard', (string) $vo);
    }

    public function test_valuation_method_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ValuationMethod::from('bogus');
    }

    public function test_valuation_method_valid_returns_array(): void
    {
        $this->assertIsArray(ValuationMethod::valid());
        $this->assertContains('fifo', ValuationMethod::valid());
    }

    // --------------- ManagementMethod VO ---------------

    public function test_management_method_standard(): void
    {
        $vo = ManagementMethod::from(ManagementMethod::STANDARD);
        $this->assertSame('standard', (string) $vo);
    }

    public function test_management_method_batch(): void
    {
        $vo = ManagementMethod::from(ManagementMethod::BATCH);
        $this->assertSame('batch', (string) $vo);
    }

    public function test_management_method_lot(): void
    {
        $vo = ManagementMethod::from(ManagementMethod::LOT);
        $this->assertSame('lot', (string) $vo);
    }

    public function test_management_method_serial(): void
    {
        $vo = ManagementMethod::from(ManagementMethod::SERIAL);
        $this->assertSame('serial', (string) $vo);
    }

    public function test_management_method_batch_and_serial(): void
    {
        $vo = ManagementMethod::from(ManagementMethod::BATCH_AND_SERIAL);
        $this->assertSame('batch_and_serial', (string) $vo);
    }

    public function test_management_method_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ManagementMethod::from('unknown');
    }

    // --------------- StockRotationStrategy VO ---------------

    public function test_stock_rotation_fifo(): void
    {
        $vo = StockRotationStrategy::from(StockRotationStrategy::FIFO);
        $this->assertSame('fifo', (string) $vo);
    }

    public function test_stock_rotation_lifo(): void
    {
        $vo = StockRotationStrategy::from(StockRotationStrategy::LIFO);
        $this->assertSame('lifo', (string) $vo);
    }

    public function test_stock_rotation_fefo(): void
    {
        $vo = StockRotationStrategy::from(StockRotationStrategy::FEFO);
        $this->assertSame('fefo', (string) $vo);
    }

    public function test_stock_rotation_lefo(): void
    {
        $vo = StockRotationStrategy::from(StockRotationStrategy::LEFO);
        $this->assertSame('lefo', (string) $vo);
    }

    public function test_stock_rotation_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        StockRotationStrategy::from('invalid');
    }

    // --------------- AllocationAlgorithm VO ---------------

    public function test_allocation_algorithm_fifo(): void
    {
        $vo = AllocationAlgorithm::from(AllocationAlgorithm::FIFO);
        $this->assertSame('fifo', (string) $vo);
    }

    public function test_allocation_algorithm_nearest(): void
    {
        $vo = AllocationAlgorithm::from(AllocationAlgorithm::NEAREST);
        $this->assertSame('nearest', (string) $vo);
    }

    public function test_allocation_algorithm_zone_based(): void
    {
        $vo = AllocationAlgorithm::from(AllocationAlgorithm::ZONE_BASED);
        $this->assertSame('zone_based', (string) $vo);
    }

    public function test_allocation_algorithm_fefo(): void
    {
        $vo = AllocationAlgorithm::from(AllocationAlgorithm::FEFO);
        $this->assertSame('fefo', (string) $vo);
    }

    public function test_allocation_algorithm_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AllocationAlgorithm::from('wrong');
    }

    // --------------- CycleCountMethod VO ---------------

    public function test_cycle_count_full(): void
    {
        $vo = CycleCountMethod::from(CycleCountMethod::FULL);
        $this->assertSame('full', (string) $vo);
    }

    public function test_cycle_count_partial(): void
    {
        $vo = CycleCountMethod::from(CycleCountMethod::PARTIAL);
        $this->assertSame('partial', (string) $vo);
    }

    public function test_cycle_count_abc(): void
    {
        $vo = CycleCountMethod::from(CycleCountMethod::ABC);
        $this->assertSame('abc', (string) $vo);
    }

    public function test_cycle_count_random(): void
    {
        $vo = CycleCountMethod::from(CycleCountMethod::RANDOM);
        $this->assertSame('random', (string) $vo);
    }

    public function test_cycle_count_periodic(): void
    {
        $vo = CycleCountMethod::from(CycleCountMethod::PERIODIC);
        $this->assertSame('periodic', (string) $vo);
    }

    public function test_cycle_count_invalid_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CycleCountMethod::from('nope');
    }

    // --------------- InventoryLevel entity ---------------

    private function makeLevel(float $onHand = 100.0, float $reserved = 20.0, float $available = 80.0): InventoryLevel
    {
        return new InventoryLevel(
            id: 1,
            tenantId: 1,
            productId: 10,
            warehouseId: 5,
            locationId: 3,
            quantityOnHand: $onHand,
            quantityReserved: $reserved,
            quantityAvailable: $available,
            quantityOnOrder: 0.0,
        );
    }

    public function test_inventory_level_reserve_reduces_available(): void
    {
        $level = $this->makeLevel();
        $level->reserve(10.0);
        $this->assertSame(70.0, $level->quantityAvailable);
    }

    public function test_inventory_level_reserve_increases_reserved(): void
    {
        $level = $this->makeLevel();
        $level->reserve(10.0);
        $this->assertSame(30.0, $level->quantityReserved);
    }

    public function test_inventory_level_reserve_insufficient_stock_throws(): void
    {
        $level = $this->makeLevel();
        $this->expectException(\DomainException::class);
        $level->reserve(200.0);
    }

    public function test_inventory_level_release_reduces_reserved(): void
    {
        $level = $this->makeLevel();
        $level->release(10.0);
        $this->assertSame(10.0, $level->quantityReserved);
    }

    public function test_inventory_level_release_increases_available(): void
    {
        $level = $this->makeLevel();
        $level->release(10.0);
        $this->assertSame(90.0, $level->quantityAvailable);
    }

    public function test_inventory_level_release_caps_at_reserved(): void
    {
        $level = $this->makeLevel();
        $level->release(999.0);
        $this->assertSame(0.0, $level->quantityReserved);
        $this->assertSame(100.0, $level->quantityAvailable);
    }

    public function test_inventory_level_adjust_updates_on_hand(): void
    {
        $level = $this->makeLevel();
        $level->adjust(150.0);
        $this->assertSame(150.0, $level->quantityOnHand);
    }

    public function test_inventory_level_adjust_recalculates_available(): void
    {
        $level = $this->makeLevel();
        $level->adjust(50.0);
        $this->assertSame(30.0, $level->quantityAvailable);
    }

    public function test_inventory_level_id_is_stored(): void
    {
        $level = $this->makeLevel();
        $this->assertSame(1, $level->id);
    }

    public function test_inventory_level_construction_defaults(): void
    {
        $level = new InventoryLevel(
            id: null,
            tenantId: 2,
            productId: 99,
            warehouseId: 1,
            locationId: 1,
            quantityOnHand: 0.0,
            quantityReserved: 0.0,
            quantityAvailable: 0.0,
            quantityOnOrder: 0.0,
        );
        $this->assertNull($level->id);
        $this->assertSame('available', $level->stockStatus);
    }
}
