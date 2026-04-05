<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Entities\ProductCategory;
use Modules\Product\Domain\Entities\ProductVariant;
use Modules\Product\Domain\Entities\ProductComponent;
use PHPUnit\Framework\TestCase;
class ProductModuleTest extends TestCase {
    public function test_product_entity(): void {
        $p = new Product(1, 1, 'SKU001', 'Laptop', 'physical', null, 500.0, 999.0, 'USD', null, true, true, 1, '1234567890', 'unit');
        $this->assertSame('SKU001', $p->getSku());
        $this->assertTrue($p->isInventoried());
        $this->assertTrue($p->isTaxable());
    }
    public function test_service_product_not_inventoried(): void {
        $p = new Product(1, 1, 'SVC001', 'Consulting', 'service', null, 0.0, 100.0, 'USD', null, true, false, null, null, 'hour');
        $this->assertFalse($p->isInventoried());
    }
    public function test_product_variant(): void {
        $v = new ProductVariant(1, 1, 'SKU001-RED-M', 'Laptop Red M', ['color'=>'red','size'=>'M'], 1099.0, 550.0, true);
        $this->assertSame(['color'=>'red','size'=>'M'], $v->getAttributes());
        $this->assertSame(1099.0, $v->getPriceOverride());
    }
    public function test_product_component(): void {
        $c = new ProductComponent(1, 10, 20, 2.0, 'unit');
        $this->assertSame(20, $c->getComponentProductId());
        $this->assertSame(2.0, $c->getQuantity());
    }
    public function test_product_category(): void {
        $cat = new ProductCategory(1, 1, 'Electronics', 'ELEC', null, '/1/', 0, true);
        $this->assertSame('ELEC', $cat->getCode());
        $this->assertSame(0, $cat->getLevel());
    }
}
