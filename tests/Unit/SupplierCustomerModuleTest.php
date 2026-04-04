<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Supplier\Domain\Entities\Supplier;
use Modules\Customer\Domain\Entities\Customer;

class SupplierCustomerModuleTest extends TestCase
{
    private function makeSupplier(array $overrides = []): Supplier
    {
        return new Supplier(
            $overrides['id'] ?? 1, $overrides['tenant_id'] ?? 1,
            $overrides['name'] ?? 'ACME Corp', $overrides['code'] ?? 'SUP-001',
            'acme@example.com', '+1-555-0000', '123 Industrial Ave',
            $overrides['is_active'] ?? true, null, null, null
        );
    }
    private function makeCustomer(array $overrides = []): Customer
    {
        return new Customer(
            $overrides['id'] ?? 1, $overrides['tenant_id'] ?? 1,
            $overrides['name'] ?? 'Globex', $overrides['code'] ?? 'CUS-001',
            'globex@example.com', '+1-555-1111', '456 Commerce Blvd',
            $overrides['is_active'] ?? true, null, null, null
        );
    }

    public function test_supplier_creation(): void
    {
        $sup = $this->makeSupplier();
        $this->assertEquals('SUP-001', $sup->getCode());
        $this->assertEquals('ACME Corp', $sup->getName());
        $this->assertTrue($sup->isActive());
        $this->assertEquals('acme@example.com', $sup->getEmail());
    }

    public function test_customer_creation(): void
    {
        $cus = $this->makeCustomer();
        $this->assertEquals('CUS-001', $cus->getCode());
        $this->assertEquals('Globex', $cus->getName());
        $this->assertTrue($cus->isActive());
    }

    public function test_supplier_deactivate(): void
    {
        $sup = $this->makeSupplier();
        $this->assertTrue($sup->isActive());
        $sup->deactivate();
        $this->assertFalse($sup->isActive());
    }

    public function test_customer_deactivate(): void
    {
        $cus = $this->makeCustomer();
        $this->assertTrue($cus->isActive());
        $cus->deactivate();
        $this->assertFalse($cus->isActive());
    }
}
