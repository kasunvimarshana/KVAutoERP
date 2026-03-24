<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

// Tenant service interfaces
use Modules\Tenant\Application\Contracts\CreateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\UpdateTenantServiceInterface;
use Modules\Tenant\Application\Contracts\DeleteTenantServiceInterface;
use Modules\Tenant\Application\Contracts\UpdateTenantConfigServiceInterface;
use Modules\Tenant\Application\Contracts\UploadTenantAttachmentServiceInterface;
use Modules\Tenant\Application\Contracts\DeleteTenantAttachmentServiceInterface;

// User service interfaces
use Modules\User\Application\Contracts\CreateUserServiceInterface;
use Modules\User\Application\Contracts\UpdateUserServiceInterface;
use Modules\User\Application\Contracts\DeleteUserServiceInterface;
use Modules\User\Application\Contracts\AssignRoleServiceInterface;
use Modules\User\Application\Contracts\UpdatePreferencesServiceInterface;
use Modules\User\Application\Contracts\UploadUserAttachmentServiceInterface;
use Modules\User\Application\Contracts\DeleteUserAttachmentServiceInterface;

// OrganizationUnit service interfaces
use Modules\OrganizationUnit\Application\Contracts\CreateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\DeleteOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\MoveOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\UploadOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\DeleteOrganizationUnitAttachmentServiceInterface;

// Tenant infrastructure
use Modules\Tenant\Infrastructure\Http\Middleware\ResolveTenant;
use Modules\Tenant\Infrastructure\Services\TenantConfig;
use Modules\Tenant\Domain\Contracts\TenantConfigInterface;

class ServiceContractsTest extends TestCase
{
    /**
     * Verify all Tenant service interface contracts exist.
     */
    public function test_all_tenant_service_interfaces_exist(): void
    {
        $this->assertTrue(interface_exists(CreateTenantServiceInterface::class));
        $this->assertTrue(interface_exists(UpdateTenantServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteTenantServiceInterface::class));
        $this->assertTrue(interface_exists(UpdateTenantConfigServiceInterface::class));
        $this->assertTrue(interface_exists(UploadTenantAttachmentServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteTenantAttachmentServiceInterface::class));
    }

    /**
     * Verify all User service interface contracts exist.
     */
    public function test_all_user_service_interfaces_exist(): void
    {
        $this->assertTrue(interface_exists(CreateUserServiceInterface::class));
        $this->assertTrue(interface_exists(UpdateUserServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteUserServiceInterface::class));
        $this->assertTrue(interface_exists(AssignRoleServiceInterface::class));
        $this->assertTrue(interface_exists(UpdatePreferencesServiceInterface::class));
        $this->assertTrue(interface_exists(UploadUserAttachmentServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteUserAttachmentServiceInterface::class));
    }

    /**
     * Verify all OrganizationUnit service interface contracts exist.
     */
    public function test_all_organization_unit_service_interfaces_exist(): void
    {
        $this->assertTrue(interface_exists(CreateOrganizationUnitServiceInterface::class));
        $this->assertTrue(interface_exists(UpdateOrganizationUnitServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteOrganizationUnitServiceInterface::class));
        $this->assertTrue(interface_exists(MoveOrganizationUnitServiceInterface::class));
        $this->assertTrue(interface_exists(UploadOrganizationUnitAttachmentServiceInterface::class));
        $this->assertTrue(interface_exists(DeleteOrganizationUnitAttachmentServiceInterface::class));
    }

    /**
     * Verify the Tenant ResolveTenant middleware exists (not the removed Core one).
     */
    public function test_tenant_resolve_tenant_middleware_exists(): void
    {
        $this->assertTrue(class_exists(ResolveTenant::class));
    }

    /**
     * Verify the Core ResolveTenant middleware file no longer exists.
     */
    public function test_core_resolve_tenant_middleware_removed(): void
    {
        $path = dirname(__DIR__, 2)
            . '/app/Modules/Core/Infrastructure/Http/Middleware/ResolveTenant.php';
        $this->assertFalse(file_exists($path), 'Core ResolveTenant middleware should have been removed.');
    }

    /**
     * Verify TenantConfigInterface and its implementation exist.
     */
    public function test_tenant_config_interface_and_implementation_exist(): void
    {
        $this->assertTrue(interface_exists(TenantConfigInterface::class));
        $this->assertTrue(class_exists(TenantConfig::class));
        $this->assertTrue(is_subclass_of(TenantConfig::class, TenantConfigInterface::class));
    }

    /**
     * Verify the unused TenantConfigRepositoryInterface file was removed.
     */
    public function test_tenant_config_repository_interface_removed(): void
    {
        $path = dirname(__DIR__, 2)
            . '/app/Modules/Tenant/Domain/Contracts/TenantConfigRepositoryInterface.php';
        $this->assertFalse(file_exists($path), 'TenantConfigRepositoryInterface should have been removed.');
    }
}
