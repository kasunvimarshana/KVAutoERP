<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\Entities\TaxGroupRate;
use Modules\Tax\Application\Services\CalculateTaxService;
use PHPUnit\Framework\TestCase;
class TaxModuleTest extends TestCase {
    public function test_tax_group_entity(): void {
        $g = new TaxGroup(1, 1, 'VAT', 'VAT', 'exclusive', false, true);
        $this->assertSame('VAT', $g->getCode());
        $this->assertFalse($g->isCompound());
    }
    public function test_simple_tax_calculation(): void {
        $svc = new CalculateTaxService();
        $result = $svc->calculate(100.0, [['name'=>'GST','rate'=>10.0]]);
        $this->assertEqualsWithDelta(10.0, $result['tax_amount'], 0.001);
        $this->assertCount(1, $result['breakdown']);
    }
    public function test_multiple_rates(): void {
        $svc = new CalculateTaxService();
        $result = $svc->calculate(100.0, [['name'=>'GST','rate'=>10.0],['name'=>'PST','rate'=>5.0]]);
        $this->assertEqualsWithDelta(15.0, $result['tax_amount'], 0.001);
        $this->assertCount(2, $result['breakdown']);
    }
    public function test_compound_tax(): void {
        $svc = new CalculateTaxService();
        $result = $svc->calculate(100.0, [['name'=>'GST','rate'=>10.0],['name'=>'PST','rate'=>5.0]], true);
        // GST = 10, base becomes 110, PST = 5.5
        $this->assertEqualsWithDelta(15.5, $result['tax_amount'], 0.001);
    }
    public function test_tax_group_rate_entity(): void {
        $r = new TaxGroupRate(1, 1, 'GST', 10.0, 1);
        $this->assertSame(10.0, $r->getRate());
        $this->assertSame(1, $r->getSequence());
    }
}
