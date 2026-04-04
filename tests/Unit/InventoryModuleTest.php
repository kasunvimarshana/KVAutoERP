<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Inventory\Domain\Entities\InventoryLevel;
use Modules\Inventory\Domain\Entities\InventoryValuationLayer;

class InventoryModuleTest extends TestCase
{
    private function makeLevel(float $onHand = 100.0, float $reserved = 0.0): InventoryLevel
    {
        return new InventoryLevel(1, 1, 1, 1, null, $onHand, $reserved, 0.0, 'fifo', null, null);
    }
    private function makeValuationLayer(float $qty = 50.0, float $cost = 10.0): InventoryValuationLayer
    {
        return new InventoryValuationLayer(1, 1, 1, 1, $qty, $qty, $cost, new \DateTimeImmutable(), null, null, null, null);
    }

    public function test_inventory_level_creation(): void
    {
        $level = $this->makeLevel(100.0, 20.0);
        $this->assertEquals(100.0, $level->getQuantityOnHand());
        $this->assertEquals(20.0, $level->getQuantityReserved());
        $this->assertEquals(80.0, $level->getAvailableQuantity());
        $this->assertEquals('fifo', $level->getValuationMethod());
    }

    public function test_receive_increases_on_hand(): void
    {
        $level = $this->makeLevel(100.0);
        $level->receive(50.0);
        $this->assertEquals(150.0, $level->getQuantityOnHand());
    }

    public function test_receive_rejects_negative_quantity(): void
    {
        $level = $this->makeLevel(100.0);
        $this->expectException(\InvalidArgumentException::class);
        $level->receive(-1.0);
    }

    public function test_issue_decreases_on_hand(): void
    {
        $level = $this->makeLevel(100.0, 0.0);
        $level->issue(30.0);
        $this->assertEquals(70.0, $level->getQuantityOnHand());
    }

    public function test_issue_fails_when_insufficient(): void
    {
        $level = $this->makeLevel(50.0, 40.0);
        $this->expectException(\DomainException::class);
        $level->issue(20.0); // available = 10, requested = 20
    }

    public function test_reserve_increases_reserved(): void
    {
        $level = $this->makeLevel(100.0, 0.0);
        $level->reserve(30.0);
        $this->assertEquals(30.0, $level->getQuantityReserved());
        $this->assertEquals(70.0, $level->getAvailableQuantity());
    }

    public function test_reserve_fails_when_insufficient(): void
    {
        $level = $this->makeLevel(50.0, 40.0);
        $this->expectException(\DomainException::class);
        $level->reserve(20.0); // available = 10, requested = 20
    }

    public function test_valuation_layer_remaining_quantity(): void
    {
        $layer = $this->makeValuationLayer(50.0, 10.0);
        $this->assertEquals(50.0, $layer->getQuantityRemaining());
        $this->assertTrue($layer->hasStock());
    }

    public function test_valuation_layer_consume(): void
    {
        $layer = $this->makeValuationLayer(50.0, 10.0);
        $layer->consume(20.0);
        $this->assertEquals(30.0, $layer->getQuantityRemaining());
        $this->assertTrue($layer->hasStock());
    }

    public function test_valuation_layer_consume_all(): void
    {
        $layer = $this->makeValuationLayer(50.0, 10.0);
        $layer->consume(50.0);
        $this->assertEquals(0.0, $layer->getQuantityRemaining());
        $this->assertFalse($layer->hasStock());
    }

    public function test_float_tolerance(): void
    {
        $this->assertEquals(0.0001, InventoryLevel::FLOAT_TOLERANCE);
    }
}
