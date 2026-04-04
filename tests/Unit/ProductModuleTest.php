<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Product\Domain\Entities\Product;
use Modules\Product\Domain\Entities\ProductCategory;
use Modules\Product\Domain\ValueObjects\ProductType;
use Modules\Product\Domain\Exceptions\ProductNotFoundException;

class ProductModuleTest extends TestCase
{
    private function makeProduct(string $type = 'physical', string $status = 'active'): Product
    {
        return new Product(
            1, 1, 1, 'Widget Pro', 'widget-pro', 'SKU-001',
            new ProductType($type), 'A great widget', $status,
            99.99, 0.1, 0.5, 'pcs', true, false, true,
            10.0, 20.0, null, null, null
        );
    }
    private function makeCategory(): ProductCategory
    {
        return new ProductCategory(1, 1, null, 'Electronics', 'electronics', null, true, 0, null, null);
    }

    public function test_product_type_value_object(): void
    {
        $t = new ProductType('physical');
        $this->assertEquals('physical', $t->getValue());
        $this->assertTrue($t->isPhysical());
        $this->assertFalse($t->isService());
    }

    public function test_product_type_validates_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ProductType('invalid_type');
    }

    public function test_product_type_service(): void
    {
        $t = new ProductType('service');
        $this->assertTrue($t->isService());
        $this->assertFalse($t->isPhysical());
    }

    public function test_product_type_to_string(): void
    {
        $t = new ProductType('digital');
        $this->assertEquals('digital', (string)$t);
    }

    public function test_product_creation(): void
    {
        $p = $this->makeProduct();
        $this->assertEquals('SKU-001', $p->getSku());
        $this->assertEquals('Widget Pro', $p->getName());
        $this->assertEquals('active', $p->getStatus());
        $this->assertEquals(99.99, $p->getBasePrice());
        $this->assertTrue($p->isTrackable());
        $this->assertTrue($p->isBatchTracked());
    }

    public function test_product_type_is_physical(): void
    {
        $p = $this->makeProduct('physical');
        $this->assertTrue($p->getType()->isPhysical());
    }

    public function test_product_category_creation(): void
    {
        $c = $this->makeCategory();
        $this->assertEquals('Electronics', $c->getName());
        $this->assertEquals('electronics', $c->getSlug());
        $this->assertNull($c->getParentId());
    }

    public function test_product_not_found_exception(): void
    {
        try {
            throw new ProductNotFoundException(42);
        } catch (ProductNotFoundException $e) {
            $this->assertStringContainsString('42', $e->getMessage());
        }
    }
}
