<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\Exceptions\RoleNotFoundException;
use Modules\Authorization\Domain\Exceptions\PermissionNotFoundException;
use Modules\Authorization\Domain\RepositoryInterfaces\RoleRepositoryInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\PermissionRepositoryInterface;
use Modules\Authorization\Application\Services\RoleService;
use Modules\Authorization\Application\Services\PermissionService;

class AuthorizationModuleTest extends TestCase
{
    private function makeRole(int $id = 1): Role
    {
        return new Role($id, 1, 'Admin', 'admin', 'Administrator role', new \DateTime(), new \DateTime());
    }

    private function makePermission(int $id = 1): Permission
    {
        return new Permission($id, 'View Users', 'users.view', 'user', 'Can view users', new \DateTime(), new \DateTime());
    }

    public function test_role_entity_getters(): void
    {
        $role = $this->makeRole();
        $this->assertEquals(1, $role->getId());
        $this->assertEquals(1, $role->getTenantId());
        $this->assertEquals('Admin', $role->getName());
        $this->assertEquals('admin', $role->getSlug());
        $this->assertEquals('Administrator role', $role->getDescription());
    }

    public function test_permission_entity_getters(): void
    {
        $permission = $this->makePermission();
        $this->assertEquals(1, $permission->getId());
        $this->assertEquals('View Users', $permission->getName());
        $this->assertEquals('users.view', $permission->getSlug());
        $this->assertEquals('user', $permission->getModule());
    }

    public function test_role_service_finds_role(): void
    {
        /** @var RoleRepositoryInterface&MockObject $repo */
        $repo = $this->createMock(RoleRepositoryInterface::class);
        $repo->expects($this->once())->method('findById')->with(1)->willReturn($this->makeRole());

        $service = new RoleService($repo);
        $result = $service->findById(1);
        $this->assertEquals(1, $result->getId());
    }

    public function test_role_service_throws_not_found(): void
    {
        /** @var RoleRepositoryInterface&MockObject $repo */
        $repo = $this->createMock(RoleRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $service = new RoleService($repo);
        $this->expectException(RoleNotFoundException::class);
        $service->findById(999);
    }

    public function test_permission_service_throws_not_found(): void
    {
        /** @var PermissionRepositoryInterface&MockObject $repo */
        $repo = $this->createMock(PermissionRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $service = new PermissionService($repo);
        $this->expectException(PermissionNotFoundException::class);
        $service->findById(999);
    }

    public function test_role_not_found_exception_message(): void
    {
        $e = new RoleNotFoundException(5);
        $this->assertStringContainsString('5', $e->getMessage());
        $this->assertStringContainsString('Role', $e->getMessage());
    }

    public function test_permission_not_found_exception_message(): void
    {
        $e = new PermissionNotFoundException(10);
        $this->assertStringContainsString('10', $e->getMessage());
        $this->assertStringContainsString('Permission', $e->getMessage());
    }
}
