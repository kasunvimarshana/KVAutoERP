<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\UoM\Domain\Entities\UnitOfMeasure;
use Modules\UoM\Domain\Entities\UomCategory;
use Modules\GS1\Domain\Entities\Gs1Label;

class UomGS1ModuleTest extends TestCase
{
    private function makeUoMCategory(): UomCategory
    {
        return new UomCategory(1, 1, 'Weight', 'weight', true, null, null);
    }
    private function makeUnitOfMeasure(float $factor = 1.0, bool $isBase = true, string $type = 'base'): UnitOfMeasure
    {
        return new UnitOfMeasure(1, 1, 1, 'Kilogram', 'kg', $isBase, $factor, $type, true, null, null);
    }
    private function makeGs1Label(): Gs1Label
    {
        return new Gs1Label(1, 1, 1, 'gtin-13', '5901234123457', 'BATCH-001', null, null, '261231', 1.5, 'LK', null, null);
    }

    // UoM tests
    public function test_uom_category_creation(): void
    {
        $cat = $this->makeUoMCategory();
        $this->assertEquals('Weight', $cat->getName());
        $this->assertEquals(1, $cat->getTenantId());
    }

    public function test_unit_of_measure_creation(): void
    {
        $uom = $this->makeUnitOfMeasure(1.0, true, 'base');
        $this->assertEquals('kg', $uom->getSymbol());
        $this->assertTrue($uom->isBase());
        $this->assertEquals(1.0, $uom->getConversionFactor());
        $this->assertTrue($uom->isActive());
    }

    public function test_unit_of_measure_convert_to_base(): void
    {
        // 1 gram = 0.001 kg (base)
        $gram = new UnitOfMeasure(2, 1, 1, 'Gram', 'g', false, 0.001, 'base', true, null, null);
        $this->assertEquals(1.0, $gram->convertToBase(1000)); // 1000g => 1kg
    }

    public function test_unit_of_measure_convert_from_base(): void
    {
        $gram = new UnitOfMeasure(2, 1, 1, 'Gram', 'g', false, 0.001, 'base', true, null, null);
        $this->assertEquals(1000.0, $gram->convertFromBase(1.0)); // 1kg => 1000g
    }

    // GS1 tests
    public function test_gs1_label_creation(): void
    {
        $label = $this->makeGs1Label();
        $this->assertEquals('gtin-13', $label->getGs1Type());
        $this->assertEquals('5901234123457', $label->getGs1Value());
        $this->assertEquals('BATCH-001', $label->getBatchNumber());
        $this->assertEquals('261231', $label->getExpiryDate());
    }

    public function test_gs1_build_barcode_gtin13(): void
    {
        $label = $this->makeGs1Label();
        $barcode = $label->buildBarcode();
        $this->assertStringContainsString('(01)5901234123457', $barcode);
        $this->assertStringContainsString('(10)BATCH-001', $barcode);
        $this->assertStringContainsString('(17)261231', $barcode);
    }

    public function test_gs1_label_net_weight(): void
    {
        $label = $this->makeGs1Label();
        $this->assertEquals(1.5, $label->getNetWeight());
        $this->assertEquals('LK', $label->getCountryOfOrigin());
    }
}
