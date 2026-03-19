<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Unit tests for TenantService.
 *
 * Tests tenant CRUD, hierarchy retrieval, and list/filter operations.
 */
class TenantServiceTest extends TestCase
{
    use RefreshDatabase;

    private TenantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TenantService();
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'id'     => (string) Str::uuid(),
            'name'   => 'Tenant ' . Str::random(4),
            'slug'   => 'tenant-' . Str::random(6),
            'status' => 'active',
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────
    // findById
    // ──────────────────────────────────────────────────────────

    public function test_find_by_id_returns_tenant(): void
    {
        $tenant = $this->makeTenant(['name' => 'Acme Corp', 'slug' => 'acme-corp']);
        $result = $this->service->findById($tenant->id);

        $this->assertIsArray($result);
        $this->assertEquals($tenant->id, $result['id']);
        $this->assertEquals('Acme Corp', $result['name']);
        $this->assertEquals('acme-corp', $result['slug']);
    }

    public function test_find_by_id_returns_null_for_missing_tenant(): void
    {
        $result = $this->service->findById((string) Str::uuid());

        $this->assertNull($result);
    }

    public function test_find_by_id_returns_expected_keys(): void
    {
        $tenant = $this->makeTenant();
        $result = $this->service->findById($tenant->id);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('slug', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('iam_provider', $result);
        $this->assertArrayHasKey('configuration', $result);
    }

    // ──────────────────────────────────────────────────────────
    // create
    // ──────────────────────────────────────────────────────────

    public function test_create_stores_tenant_in_database(): void
    {
        $result = $this->service->create([
            'name'   => 'TechCorp',
            'slug'   => 'techcorp',
            'status' => 'active',
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('TechCorp', $result['name']);
        $this->assertEquals('techcorp', $result['slug']);
        $this->assertEquals('active', $result['status']);
        $this->assertDatabaseHas('tenants', ['slug' => 'techcorp']);
    }

    public function test_create_auto_generates_slug_from_name(): void
    {
        $result = $this->service->create([
            'name' => 'Auto Slug Corp',
        ]);

        $this->assertEquals('auto-slug-corp', $result['slug']);
    }

    public function test_create_defaults_iam_provider_to_local(): void
    {
        $result = $this->service->create(['name' => 'Default IAM', 'slug' => 'default-iam']);

        $this->assertEquals('local', $result['iam_provider']);
    }

    public function test_create_stores_custom_iam_provider(): void
    {
        $result = $this->service->create([
            'name'         => 'Okta Corp',
            'slug'         => 'okta-corp',
            'iam_provider' => 'okta',
        ]);

        $this->assertEquals('okta', $result['iam_provider']);
    }

    public function test_create_stores_configuration(): void
    {
        $config = ['feature_flags' => ['beta' => true]];
        $result = $this->service->create([
            'name'          => 'Config Corp',
            'slug'          => 'config-corp',
            'configuration' => $config,
        ]);

        $this->assertEquals($config, $result['configuration']);
    }

    // ──────────────────────────────────────────────────────────
    // update
    // ──────────────────────────────────────────────────────────

    public function test_update_modifies_tenant_name(): void
    {
        $tenant = $this->makeTenant(['name' => 'Old Name']);
        $result = $this->service->update($tenant->id, ['name' => 'New Name']);

        $this->assertEquals('New Name', $result['name']);
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'name' => 'New Name']);
    }

    public function test_update_changes_status(): void
    {
        $tenant = $this->makeTenant(['status' => 'active']);
        $result = $this->service->update($tenant->id, ['status' => 'suspended']);

        $this->assertEquals('suspended', $result['status']);
    }

    public function test_update_stores_iam_configuration(): void
    {
        $tenant = $this->makeTenant();
        $config = ['iam' => ['domain' => 'company.okta.com']];

        $result = $this->service->update($tenant->id, [
            'iam_provider'  => 'okta',
            'configuration' => $config,
        ]);

        $this->assertEquals('okta', $result['iam_provider']);
        $this->assertEquals($config, $result['configuration']);
    }

    // ──────────────────────────────────────────────────────────
    // delete
    // ──────────────────────────────────────────────────────────

    public function test_delete_removes_tenant(): void
    {
        $tenant = $this->makeTenant();
        $this->service->delete($tenant->id);

        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id, 'deleted_at' => null]);
    }

    // ──────────────────────────────────────────────────────────
    // list
    // ──────────────────────────────────────────────────────────

    public function test_list_returns_paginated_tenants(): void
    {
        $this->makeTenant(['status' => 'active']);
        $this->makeTenant(['status' => 'active']);

        $result = $this->service->list([], 20);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['pagination']['total']);
    }

    public function test_list_filters_by_status(): void
    {
        $this->makeTenant(['status' => 'active']);
        $this->makeTenant(['status' => 'inactive']);

        $result = $this->service->list(['status' => 'active'], 20);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('active', $result['data'][0]['status']);
    }

    public function test_list_filters_by_search(): void
    {
        $this->makeTenant(['name' => 'Acme Industries', 'slug' => 'acme-industries']);
        $this->makeTenant(['name' => 'Beta Solutions',  'slug' => 'beta-solutions']);

        $result = $this->service->list(['search' => 'Acme'], 20);

        $this->assertCount(1, $result['data']);
        $this->assertEquals('Acme Industries', $result['data'][0]['name']);
    }

    // ──────────────────────────────────────────────────────────
    // getHierarchy
    // ──────────────────────────────────────────────────────────

    public function test_get_hierarchy_returns_tenant_with_empty_organizations(): void
    {
        $tenant = $this->makeTenant(['name' => 'Hierarchy Corp', 'slug' => 'hierarchy-corp']);
        $result = $this->service->getHierarchy($tenant->id);

        $this->assertEquals($tenant->id, $result['id']);
        $this->assertEquals('Hierarchy Corp', $result['name']);
        $this->assertArrayHasKey('organizations', $result);
        $this->assertIsArray($result['organizations']);
        $this->assertEmpty($result['organizations']);
    }
}
