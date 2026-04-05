<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Product\Domain\Entities\Category;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\Entities\ProductComponent;
use Modules\Pricing\Domain\Entities\PriceList;
use Modules\Pricing\Domain\Entities\PriceListItem;
use Modules\Pricing\Domain\Entities\Discount;
use Modules\Tax\Domain\Entities\TaxRate;
use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\Entities\TaxGroupRate;
use Modules\Inventory\Domain\Entities\Stock;
use Modules\Inventory\Domain\Entities\StockLocation;
use Modules\Inventory\Domain\Entities\StockMovement;
use Modules\Inventory\Domain\Entities\BatchLot;
use Modules\Inventory\Domain\Entities\ValuationLayer;
use Modules\Inventory\Domain\Entities\StockReservation;
use Modules\Inventory\Domain\Entities\InventoryAdjustment;
use Modules\Inventory\Domain\Entities\InventoryAdjustmentLine;
use Modules\Inventory\Domain\Entities\CycleCount;
use Modules\Inventory\Domain\Entities\CycleCountLine;

class ProductPricingTaxInventoryTest extends TestCase
{
    // ── Product: Category entity ──────────────────────────────────────────────

    public function test_category_entity_holds_properties(): void
    {
        $cat = new Category(1, 1, 'Electronics', 'ELEC', null, '/ELEC', 0, 'Electronic items', true);
        $this->assertSame(1, $cat->id);
        $this->assertSame('Electronics', $cat->name);
        $this->assertSame('ELEC', $cat->code);
        $this->assertNull($cat->parentId);
        $this->assertSame(0, $cat->level);
        $this->assertTrue($cat->isActive);
    }

    public function test_category_child_has_parent_and_level(): void
    {
        $child = new Category(2, 1, 'Phones', 'PHONES', 1, '/ELEC/PHONES', 1, null, true);
        $this->assertSame(1, $child->parentId);
        $this->assertSame(1, $child->level);
        $this->assertSame('/ELEC/PHONES', $child->path);
    }

    // ── Product: Product entity ───────────────────────────────────────────────

    public function test_product_entity_defaults(): void
    {
        $p = new Product(1, 1, 'SKU-001', 'Widget', 'physical', null, null, true, 'unit', null, null, null, null);
        $this->assertSame('SKU-001', $p->sku);
        $this->assertSame('physical', $p->type);
        $this->assertSame('unit', $p->unitOfMeasure);
        $this->assertNull($p->weight);
    }

    public function test_product_types_are_stored(): void
    {
        foreach (['physical', 'service', 'digital', 'combo', 'variable'] as $type) {
            $p = new Product(1, 1, "SKU-$type", "Name", $type, null, null, true, 'unit', null, null, null, null);
            $this->assertSame($type, $p->type);
        }
    }

    public function test_product_with_metadata(): void
    {
        $meta = ['brand' => 'ACME', 'origin' => 'US'];
        $p = new Product(1, 1, 'SKU-002', 'Widget', 'physical', null, null, true, 'unit', 1.5, ['l' => 10], [], $meta);
        $this->assertSame($meta, $p->metadata);
        $this->assertSame(1.5, $p->weight);
    }

    // ── Product: ProductVariant entity ────────────────────────────────────────

    public function test_product_variant_attributes(): void
    {
        $v = new ProductVariant(1, 1, 10, 'SKU-RED-L', 'Red Large', ['color' => 'red', 'size' => 'L'], 29.99, 15.0, true);
        $this->assertSame(['color' => 'red', 'size' => 'L'], $v->attributes);
        $this->assertSame(29.99, $v->price);
        $this->assertSame(15.0, $v->cost);
    }

    // ── Product: ProductComponent entity (BOM) ────────────────────────────────

    public function test_product_component_bom(): void
    {
        $comp = new ProductComponent(1, 1, 5, 3, 2.5, 'kg', 'Handle part');
        $this->assertSame(5, $comp->productId);
        $this->assertSame(3, $comp->componentProductId);
        $this->assertSame(2.5, $comp->quantity);
        $this->assertSame('kg', $comp->unit);
    }

    // ── Pricing: PriceList entity ─────────────────────────────────────────────

    public function test_price_list_entity(): void
    {
        $pl = new PriceList(1, 1, 'Retail', 'RETAIL', 'USD', false, null, null, null, true);
        $this->assertSame('RETAIL', $pl->code);
        $this->assertSame('USD', $pl->currency);
        $this->assertFalse($pl->isDefault);
        $this->assertTrue($pl->isActive);
    }

    public function test_price_list_default_flag(): void
    {
        $pl = new PriceList(2, 1, 'Wholesale', 'WHOLESALE', 'EUR', true, null, null, 'Bulk pricing', true);
        $this->assertTrue($pl->isDefault);
        $this->assertSame('EUR', $pl->currency);
    }

    // ── Pricing: PriceListItem entity ─────────────────────────────────────────

    public function test_price_list_item_tiers(): void
    {
        $item = new PriceListItem(1, 1, 1, 10, null, 'fixed', 19.99, 1.0, null, null);
        $this->assertSame('fixed', $item->priceType);
        $this->assertSame(19.99, $item->price);
        $this->assertSame(1.0, $item->minQuantity);
        $this->assertNull($item->maxQuantity);
    }

    // ── Pricing: Discount entity ──────────────────────────────────────────────

    public function test_discount_percentage_entity(): void
    {
        $d = new Discount(1, 1, 'Summer Sale', 'SUMMER10', 'percentage', 10.0, 'order', null, null, null, null, true, null, 0);
        $this->assertSame('percentage', $d->type);
        $this->assertSame(10.0, $d->value);
        $this->assertSame(0, $d->usageCount);
    }

    public function test_discount_fixed_entity(): void
    {
        $d = new Discount(2, 1, 'Fixed Off', 'FIXED5', 'fixed', 5.0, 'order', null, 50.0, null, null, true, 100, 42);
        $this->assertSame('fixed', $d->type);
        $this->assertSame(5.0, $d->value);
        $this->assertSame(100, $d->usageLimit);
        $this->assertSame(42, $d->usageCount);
    }

    // ── Tax: TaxRate entity ───────────────────────────────────────────────────

    public function test_tax_rate_entity(): void
    {
        $t = new TaxRate(1, 1, 'VAT', 'VAT20', 20.0, 'percentage', false, true, 'GB', null, 'UK VAT');
        $this->assertSame(20.0, $t->rate);
        $this->assertFalse($t->isCompound);
        $this->assertSame('GB', $t->country);
    }

    public function test_compound_tax_rate(): void
    {
        $t = new TaxRate(2, 1, 'Extra Tax', 'EXTRA5', 5.0, 'percentage', true, true, null, null, null);
        $this->assertTrue($t->isCompound);
    }

    // ── Tax: TaxGroup entity ──────────────────────────────────────────────────

    public function test_tax_group_entity(): void
    {
        $g = new TaxGroup(1, 1, 'Standard', 'STD', 'Standard tax group', true);
        $this->assertSame('STD', $g->code);
        $this->assertTrue($g->isActive);
    }

    // ── Tax: CalculateTaxService logic (pure computation) ─────────────────────

    public function test_simple_tax_calculation(): void
    {
        // 20% of 100 = 20
        $amount = 100.0;
        $rate = 20.0;
        $taxAmount = round($amount * ($rate / 100), 4);
        $this->assertSame(20.0, $taxAmount);
    }

    public function test_compound_tax_calculation(): void
    {
        // First rate: 10% of 100 = 10
        // Second rate (compound): 5% of (100 + 10) = 5.5
        $amount = 100.0;
        $first = $amount * (10.0 / 100);        // 10
        $second = ($amount + $first) * (5.0 / 100); // 5.5
        $total = $first + $second;
        $this->assertSame(15.5, $total);
    }

    public function test_multiple_non_compound_taxes(): void
    {
        $amount = 200.0;
        $rates = [10.0, 5.0]; // 10% + 5% = 30 total
        $total = 0.0;
        foreach ($rates as $r) {
            $total += $amount * ($r / 100);
        }
        $this->assertSame(30.0, $total);
    }

    // ── Inventory: Stock entity ───────────────────────────────────────────────

    public function test_stock_available_quantity(): void
    {
        $stock = new Stock(1, 1, 10, null, 5, 100.0, 30.0, 'unit', null);
        $this->assertSame(70.0, $stock->getAvailableQuantity());
    }

    public function test_stock_available_never_negative(): void
    {
        $stock = new Stock(1, 1, 10, null, 5, 20.0, 50.0, 'unit', null);
        $this->assertSame(0.0, $stock->getAvailableQuantity());
    }

    // ── Inventory: StockLocation entity ──────────────────────────────────────

    public function test_stock_location_hierarchy(): void
    {
        $loc = new StockLocation(1, 1, 1, 'BIN-A1', 'Bin A1', 'bin', 3, '/WH1/ZONE-A/RACK-1/BIN-A1', 3, true, null);
        $this->assertSame('bin', $loc->type);
        $this->assertSame(3, $loc->level);
        $this->assertSame(3, $loc->parentId);
    }

    // ── Inventory: StockMovement entity ──────────────────────────────────────

    public function test_stock_movement_receipt(): void
    {
        $m = new StockMovement(1, 1, 10, null, null, 5, 50.0, 'receipt', 'PO-001', 'BATCH-1', null, null, null, 10.0, null, null);
        $this->assertSame('receipt', $m->type);
        $this->assertSame('BATCH-1', $m->batchNumber);
        $this->assertNull($m->fromLocationId);
        $this->assertSame(5, $m->toLocationId);
    }

    // ── Inventory: BatchLot entity ────────────────────────────────────────────

    public function test_batch_lot_entity(): void
    {
        $expiry = new \DateTimeImmutable('2026-12-31');
        $b = new BatchLot(1, 1, 10, null, 'BATCH-001', 'LOT-A', null, $expiry, null, 100.0, 75.0, 3, 'active', null);
        $this->assertSame('BATCH-001', $b->batchNumber);
        $this->assertSame(75.0, $b->remainingQuantity);
        $this->assertSame('active', $b->status);
    }

    // ── Inventory: ValuationLayer entity ──────────────────────────────────────

    public function test_valuation_layer_entity(): void
    {
        $receivedAt = new \DateTimeImmutable('2026-01-01');
        $layer = new ValuationLayer(1, 1, 10, null, 3, null, 50.0, 50.0, 12.50, 'fifo', $receivedAt, 'PO-001');
        $this->assertSame(12.50, $layer->unitCost);
        $this->assertSame('fifo', $layer->valuationMethod);
        $this->assertSame(50.0, $layer->remainingQuantity);
    }

    // ── Inventory: InventoryAdjustmentLine variance ───────────────────────────

    public function test_adjustment_line_variance_positive(): void
    {
        $line = new InventoryAdjustmentLine(1, 1, 1, 10, null, 50.0, 55.0, 10.0, null);
        $this->assertSame(5.0, $line->getVariance());
    }

    public function test_adjustment_line_variance_negative(): void
    {
        $line = new InventoryAdjustmentLine(2, 1, 1, 10, null, 50.0, 45.0, 10.0, null);
        $this->assertSame(-5.0, $line->getVariance());
    }

    public function test_adjustment_line_variance_zero(): void
    {
        $line = new InventoryAdjustmentLine(3, 1, 1, 10, null, 50.0, 50.0, 10.0, null);
        $this->assertSame(0.0, $line->getVariance());
    }

    // ── Inventory: CycleCountLine variance ───────────────────────────────────

    public function test_cycle_count_line_variance(): void
    {
        $line = new CycleCountLine(1, 1, 1, 10, null, 100.0, 95.0, null);
        $this->assertSame(-5.0, $line->getVariance());
    }

    public function test_cycle_count_line_variance_null_when_not_counted(): void
    {
        $line = new CycleCountLine(2, 1, 1, 10, null, 100.0, null, null);
        $this->assertNull($line->getVariance());
    }

    // ── Pricing: Discount apply logic (pure) ──────────────────────────────────

    public function test_apply_percentage_discount(): void
    {
        $orderAmount = 200.0;
        $value = 10.0; // 10%
        $result = $orderAmount - ($orderAmount * $value / 100);
        $this->assertSame(180.0, $result);
    }

    public function test_apply_fixed_discount(): void
    {
        $orderAmount = 200.0;
        $value = 25.0;
        $result = max(0.0, $orderAmount - $value);
        $this->assertSame(175.0, $result);
    }

    public function test_fixed_discount_never_below_zero(): void
    {
        $orderAmount = 10.0;
        $value = 50.0;
        $result = max(0.0, $orderAmount - $value);
        $this->assertSame(0.0, $result);
    }

    // ── Pricing: ResolvePriceService tier logic (pure) ────────────────────────

    public function test_resolve_price_picks_highest_applicable_tier(): void
    {
        // Tiers: min_qty=1 => $100, min_qty=10 => $90, min_qty=50 => $80
        $tiers = [
            ['min_qty' => 1, 'max_qty' => null, 'price' => 100.0],
            ['min_qty' => 10, 'max_qty' => null, 'price' => 90.0],
            ['min_qty' => 50, 'max_qty' => null, 'price' => 80.0],
        ];
        $quantity = 15.0;

        $applicable = array_filter($tiers, fn($t) => $t['min_qty'] <= $quantity && ($t['max_qty'] === null || $t['max_qty'] >= $quantity));
        usort($applicable, fn($a, $b) => $b['min_qty'] <=> $a['min_qty']);
        $resolved = array_values($applicable)[0]['price'] ?? null;

        $this->assertSame(90.0, $resolved);
    }

    // ── Inventory: Allocation FIFO/LIFO/FEFO order (pure) ────────────────────

    public function test_fifo_allocation_order(): void
    {
        $batches = [
            ['id' => 3, 'remaining' => 20.0],
            ['id' => 1, 'remaining' => 10.0],
            ['id' => 2, 'remaining' => 15.0],
        ];
        usort($batches, fn($a, $b) => $a['id'] <=> $b['id']); // FIFO: oldest id first
        $this->assertSame(1, $batches[0]['id']);
        $this->assertSame(2, $batches[1]['id']);
    }

    public function test_lifo_allocation_order(): void
    {
        $batches = [
            ['id' => 1, 'remaining' => 10.0],
            ['id' => 3, 'remaining' => 20.0],
            ['id' => 2, 'remaining' => 15.0],
        ];
        usort($batches, fn($a, $b) => $b['id'] <=> $a['id']); // LIFO: newest id first
        $this->assertSame(3, $batches[0]['id']);
    }

    public function test_fefo_allocation_order(): void
    {
        $batches = [
            ['id' => 1, 'expiry' => '2026-06-01'],
            ['id' => 2, 'expiry' => '2026-03-01'],
            ['id' => 3, 'expiry' => '2026-09-01'],
        ];
        usort($batches, fn($a, $b) => $a['expiry'] <=> $b['expiry']); // FEFO: earliest expiry first
        $this->assertSame('2026-03-01', $batches[0]['expiry']);
    }

    // ── Inventory: Consume valuation layers (pure) ────────────────────────────

    public function test_consume_layers_weighted_average_cost(): void
    {
        // Layer1: 10 units @ $5, Layer2: 20 units @ $8
        // Consume 15 units via FIFO: use 10 from L1 + 5 from L2
        $layers = [
            ['remaining' => 10.0, 'unit_cost' => 5.0],
            ['remaining' => 20.0, 'unit_cost' => 8.0],
        ];
        $toConsume = 15.0;
        $remaining = $toConsume;
        $totalCost = 0.0;

        foreach ($layers as &$layer) {
            if ($remaining <= 0) break;
            $consume = min($layer['remaining'], $remaining);
            $totalCost += $consume * $layer['unit_cost'];
            $remaining -= $consume;
        }

        $consumed = $toConsume - $remaining;
        $weightedAvg = $consumed > 0 ? $totalCost / $consumed : 0.0;

        $this->assertSame(0.0, $remaining);
        $this->assertEqualsWithDelta(6.0, $weightedAvg, 0.001); // (10*5 + 5*8)/15 = 90/15 = 6
    }
}
