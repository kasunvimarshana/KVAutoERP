<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use KvEnterprise\SharedKernel\ValueObjects\TenantId;

/**
 * Abstract base test case for the User Service.
 *
 * Provides shared helpers for tenant-context setup, JWT claim injection,
 * and middleware bypassing used across unit and feature test suites.
 */
abstract class TestCase extends BaseTestCase
{
    /** Fixed tenant UUID (UUID v4) used across all tests for deterministic scoping. */
    protected const TEST_TENANT_ID = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

    /** Fixed organization UUID (UUID v4) used in tests. */
    protected const TEST_ORG_ID = 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11';

    /** Fixed actor (user) UUID for audit fields. */
    protected const TEST_ACTOR_ID = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';

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
     * TenantAwareModel global scopes can resolve it.
     *
     * @param  string  $tenantId
     * @return void
     */
    protected function setTenantContext(string $tenantId = self::TEST_TENANT_ID): void
    {
        $this->app->instance(TenantId::class, TenantId::fromString($tenantId));
    }

    /**
     * Return a fake JWT claims array for request attribute injection.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function makeClaims(array $overrides = []): array
    {
        return array_merge([
            'sub'             => self::TEST_ACTOR_ID,
            'user_id'         => self::TEST_ACTOR_ID,
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'branch_id'       => null,
            'roles'           => ['admin'],
            'permissions'     => ['users.manage'],
            'device_id'       => 'device-001',
            'token_version'   => 1,
            'iss'             => 'https://test.kv-enterprise.io',
            'exp'             => time() + 900,
            'iat'             => time(),
            'jti'             => 'test-jti-00001',
        ], $overrides);
    }
}
