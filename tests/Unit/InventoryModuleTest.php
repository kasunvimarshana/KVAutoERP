<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;
use Modules\Inventory\Domain\Entities\StockItem;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Application\Services\AddValuationLayerService;
use Modules\Inventory\Application\Services\AllocateStockService;
use Modules\Inventory\Application\Services\ConsumeValuationLayersService;
use PHPUnit\Framework\TestCase;
class InventoryModuleTest extends TestCase {
    public function test_stock_item_entity(): void {
        $item = new StockItem(1, 1, 5, null, 1, null, 100.0, 20.0, 80.0, 'unit');
        $this->assertSame(100.0, $item->getQuantity());
        $this->assertSame(80.0, $item->getAvailableQuantity());
    }
    public function test_stock_movement_types(): void {
        $types = ['receive','issue','transfer','adjustment','return','cycle_count'];
        foreach ($types as $type) {
            $m = new StockMovement(null, 1, 1, null, 1, null, $type, 10.0, 5.0, 'REF', null, null, null, null, new \DateTimeImmutable(), null);
            $this->assertSame($type, $m->getType());
        }
    }
    public function test_valuation_layer_is_empty(): void {
        $layer = new ValuationLayer(1, 1, 1, null, 1, 10.0, 0.0, 5.0, new \DateTimeImmutable(), null, null, null);
        $this->assertTrue($layer->isEmpty());
    }
    public function test_valuation_layer_with_remaining(): void {
        $layer = new ValuationLayer(1, 1, 1, null, 1, 10.0, 10.0, 5.0, new \DateTimeImmutable(), null, null, null);
        $this->assertFalse($layer->isEmpty());
        $updated = $layer->withRemainingQuantity(3.0);
        $this->assertSame(3.0, $updated->getRemainingQuantity());
        $this->assertSame(10.0, $updated->getQuantity()); // original unchanged
    }
    public function test_consume_fifo(): void {
        $layers = [
            new ValuationLayer(1, 1, 1, null, 1, 10.0, 10.0, 5.0, new \DateTimeImmutable('2024-01-01'), 'B001', null, null),
            new ValuationLayer(2, 1, 1, null, 1, 10.0, 10.0, 7.0, new \DateTimeImmutable('2024-02-01'), 'B002', null, null),
        ];
        $savedLayers = $layers;
        $repo = new class($savedLayers) implements \Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface {
            public array $layers;
            public function __construct(array $layers) { $this->layers = $layers; }
            public function findActiveByProduct(int $tenantId, int $productId, int $warehouseId): array { return $this->layers; }
            public function findActiveByProductDesc(int $tenantId, int $productId, int $warehouseId): array { return array_reverse($this->layers); }
            public function findActiveByExpiry(int $tenantId, int $productId, int $warehouseId): array { return $this->layers; }
            public function save(\Modules\Inventory\Domain\Entities\ValuationLayer $layer): \Modules\Inventory\Domain\Entities\ValuationLayer {
                foreach ($this->layers as $i=>$l) { if($l->getId()===$layer->getId()) { $this->layers[$i]=$layer; break; } }
                return $layer;
            }
        };
        $svc = new ConsumeValuationLayersService($repo);
        $avgCost = $svc->consume(1, 1, 1, 15.0, 'fifo');
        // 10 units at 5.0 + 5 units at 7.0 = 50+35=85, avg = 85/15 = 5.666...
        $this->assertEqualsWithDelta(85.0/15.0, $avgCost, 0.001);
    }
    public function test_allocate_fefo(): void {
        $layers = [
            new ValuationLayer(1, 1, 1, null, 1, 10.0, 10.0, 5.0, new \DateTimeImmutable(), 'B001', null, new \DateTimeImmutable('+30 days')),
            new ValuationLayer(2, 1, 1, null, 1, 10.0, 10.0, 6.0, new \DateTimeImmutable(), 'B002', null, new \DateTimeImmutable('+60 days')),
        ];
        $repo = new class($layers) implements \Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface {
            public function __construct(private array $layers) {}
            public function findActiveByProduct(int $t, int $p, int $w): array { return $this->layers; }
            public function findActiveByProductDesc(int $t, int $p, int $w): array { return array_reverse($this->layers); }
            public function findActiveByExpiry(int $t, int $p, int $w): array { return $this->layers; }
            public function save(\Modules\Inventory\Domain\Entities\ValuationLayer $l): \Modules\Inventory\Domain\Entities\ValuationLayer { return $l; }
        };
        $svc = new AllocateStockService($repo);
        $plan = $svc->allocate(1, 1, 1, 15.0, 'fefo');
        $this->assertCount(2, $plan);
        $this->assertSame('B001', $plan[0]['batch']);
        $this->assertSame(10.0, $plan[0]['qty']);
        $this->assertSame(5.0, $plan[1]['qty']);
    }
    public function test_cycle_count_entity(): void {
        $cc = new CycleCount(1, 1, 1, 'pending', 'CC-001', new \DateTimeImmutable(), null);
        $this->assertSame('pending', $cc->getStatus());
    }
    public function test_cycle_count_line_variance(): void {
        $line = new CycleCountLine(1, 1, 5, null, 100.0, 95.0, -5.0);
        $this->assertTrue($line->hasVariance());
        $this->assertSame(-5.0, $line->getVariance());
    }
    public function test_add_valuation_layer(): void {
        $savedLayer = null;
        $repo = new class($savedLayer) implements \Modules\Inventory\Domain\RepositoryInterfaces\ValuationLayerRepositoryInterface {
            public ?\Modules\Inventory\Domain\Entities\ValuationLayer $saved = null;
            public function findActiveByProduct(int $t, int $p, int $w): array { return []; }
            public function findActiveByProductDesc(int $t, int $p, int $w): array { return []; }
            public function findActiveByExpiry(int $t, int $p, int $w): array { return []; }
            public function save(\Modules\Inventory\Domain\Entities\ValuationLayer $l): \Modules\Inventory\Domain\Entities\ValuationLayer { $this->saved=$l; return $l; }
        };
        $svc = new AddValuationLayerService($repo);
        $layer = $svc->add(['tenant_id'=>1,'product_id'=>1,'warehouse_id'=>1,'quantity'=>50.0,'unit_cost'=>10.0]);
        $this->assertSame(50.0, $layer->getQuantity());
        $this->assertSame(10.0, $layer->getUnitCost());
    }
}
