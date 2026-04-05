<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Configuration\Domain\Entities\OrgUnit;
use Modules\Configuration\Domain\Entities\Setting;
use PHPUnit\Framework\TestCase;
class ConfigurationModuleTest extends TestCase {
    public function test_org_unit_entity(): void {
        $unit = new OrgUnit(1, 1, 'Engineering', 'ENG', 'department', null, '/1/', 0, true);
        $this->assertSame('Engineering', $unit->getName());
        $this->assertSame('ENG', $unit->getCode());
        $this->assertSame('department', $unit->getType());
        $this->assertTrue($unit->isActive());
    }
    public function test_org_unit_descendant_check(): void {
        $parent = new OrgUnit(1, 1, 'Parent', 'P', 'division', null, '/1/', 0, true);
        $child = new OrgUnit(2, 1, 'Child', 'C', 'department', 1, '/1/2/', 1, true);
        $this->assertTrue($child->isDescendantOf($parent));
        $this->assertFalse($parent->isDescendantOf($child));
    }
    public function test_org_unit_path_hierarchy(): void {
        $grandchild = new OrgUnit(3, 1, 'GC', 'GC', 'team', 2, '/1/2/3/', 2, true);
        $grandparent = new OrgUnit(1, 1, 'GP', 'GP', 'company', null, '/1/', 0, true);
        $this->assertTrue($grandchild->isDescendantOf($grandparent));
    }
    public function test_setting_entity(): void {
        $setting = new Setting(1, 1, 'app.timezone', 'UTC', 'app', 'string');
        $this->assertSame('app.timezone', $setting->getKey());
        $this->assertSame('UTC', $setting->getValue());
    }
}
