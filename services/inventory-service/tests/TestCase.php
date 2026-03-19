<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use KvEnterprise\SharedKernel\ValueObjects\TenantId;

/**
 * Abstract base test case for the Inventory Service.
 *
 * Provides shared helpers for tenant-context setup, JWT claim injection,
 * and middleware bypassing used across unit and feature test suites.
 */
abstract class TestCase extends BaseTestCase
{
    /** Fixed tenant UUID (UUID v4) used across all tests for deterministic scoping. */
    protected const TEST_TENANT_ID = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

    /** Fixed organization UUID (UUID v4). */
    protected const TEST_ORG_ID = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';

    /** Fixed warehouse UUID for tests. */
    protected const TEST_WAREHOUSE_ID = 'c1234567-1234-4234-8234-1234567890ab';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Bind the test tenant ID into the service container so that
     * TenantAwareModel global scopes and tenant-injection logic can resolve it.
     *
     * @param  string  $tenantId  UUID string for the current test tenant.
     * @return void
     */
    protected function setTenantContext(string $tenantId = self::TEST_TENANT_ID): void
    {
        $this->app->instance(TenantId::class, TenantId::fromString($tenantId));
    }

    /**
     * Return a fake JWT claims array for use in request attribute injection.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function makeClaims(array $overrides = []): array
    {
        return array_merge([
            'sub'             => 'user-uuid-0001',
            'user_id'         => 'user-uuid-0001',
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'branch_id'       => null,
            'roles'           => ['admin'],
            'permissions'     => ['inventory.manage'],
            'device_id'       => 'device-001',
            'token_version'   => 1,
            'iss'             => 'kv-enterprise-auth',
            'exp'             => time() + 900,
            'iat'             => time(),
            'jti'             => 'test-jti-00001',
        ], $overrides);
    }

    /**
     * Return a base warehouse data array for test fixtures.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function warehouseData(array $overrides = []): array
    {
        return array_merge([
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'code'            => 'WH-001',
            'name'            => 'Main Warehouse',
            'type'            => 'standard',
            'status'          => 'active',
            'is_default'      => true,
        ], $overrides);
    }
}
