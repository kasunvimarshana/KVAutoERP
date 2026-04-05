<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Tenant\Domain\Entities\Tenant;
use PHPUnit\Framework\TestCase;
class TenantModuleTest extends TestCase {
    public function test_tenant_entity_can_be_constructed(): void {
        $tenant = new Tenant(1, 'Acme Corp', 'acme', 'professional', true, ['theme'=>'dark'], null, new \DateTimeImmutable());
        $this->assertSame(1, $tenant->getId());
        $this->assertSame('Acme Corp', $tenant->getName());
        $this->assertSame('acme', $tenant->getSlug());
        $this->assertSame('professional', $tenant->getPlan());
        $this->assertTrue($tenant->isActive());
        $this->assertSame(['theme'=>'dark'], $tenant->getSettings());
    }
}
