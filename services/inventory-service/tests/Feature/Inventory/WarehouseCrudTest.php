<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Http\Middleware\VerifyJwtMiddleware;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use KvEnterprise\SharedKernel\Http\Middleware\RequirePermissionMiddleware;
use KvEnterprise\SharedKernel\Http\Middleware\TenantContextMiddleware;
use Tests\TestCase;

/**
 * Feature tests for the Warehouse CRUD endpoints.
 *
 * All tests bypass JWT and TenantContext middleware; the tenant context
 * is injected directly into the service container so the model global
 * scopes still enforce per-tenant isolation.
 *
 * Database: SQLite in-memory (configured in phpunit.xml).
 */
final class WarehouseCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setTenantContext();

        $this->withoutMiddleware([
            VerifyJwtMiddleware::class,
            TenantContextMiddleware::class,
            RequirePermissionMiddleware::class,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/warehouses
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_an_empty_paginated_warehouse_list(): void
    {
        $response = $this->getJson('/api/v1/warehouses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta' => [
                    'pagination' => [
                        'page',
                        'per_page',
                        'total',
                        'last_page',
                        'from',
                        'to',
                        'has_next_page',
                        'has_previous_page',
                    ],
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'meta'   => ['pagination' => ['total' => 0]],
            ]);
    }

    /** @test */
    public function it_returns_only_warehouses_belonging_to_the_current_tenant(): void
    {
        // Create a warehouse for the test tenant.
        Warehouse::create($this->warehouseData(['code' => 'WH-T1', 'name' => 'Tenant A Warehouse']));

        // Create a warehouse for a different tenant (bypassing global scope).
        Warehouse::withoutGlobalScopes()->create($this->warehouseData([
            'code'      => 'WH-T2',
            'name'      => 'Tenant B Warehouse',
            'tenant_id' => 'b1234567-1234-4234-8234-1234567890ab',
        ]));

        $response = $this->getJson('/api/v1/warehouses');

        $response->assertStatus(200)
            ->assertJson(['meta' => ['pagination' => ['total' => 1]]]);

        $data = $response->json('data');
        self::assertCount(1, $data);
        self::assertSame('WH-T1', $data[0]['code']);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/warehouses
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_a_warehouse_successfully(): void
    {
        $payload = [
            'code'            => 'WH-NEW',
            'name'            => 'New Warehouse',
            'type'            => 'standard',
            'status'          => 'active',
            'organization_id' => self::TEST_ORG_ID,
        ];

        $response = $this->postJson('/api/v1/warehouses', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'code' => 'WH-NEW',
                    'name' => 'New Warehouse',
                    'type' => 'standard',
                ],
            ]);

        $this->assertDatabaseHas('warehouses', [
            'code'      => 'WH-NEW',
            'tenant_id' => self::TEST_TENANT_ID,
        ]);
    }

    /** @test */
    public function it_rejects_a_duplicate_warehouse_code(): void
    {
        Warehouse::create($this->warehouseData(['code' => 'WH-DUP']));

        $response = $this->postJson('/api/v1/warehouses', [
            'code'            => 'WH-DUP',
            'name'            => 'Duplicate',
            'organization_id' => self::TEST_ORG_ID,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_required_fields_on_create(): void
    {
        $response = $this->postJson('/api/v1/warehouses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/warehouses/{id}
    // -------------------------------------------------------------------------

    /** @test */
    public function it_shows_a_single_warehouse(): void
    {
        $warehouse = Warehouse::create($this->warehouseData(['code' => 'WH-SHOW']));

        $response = $this->getJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => ['code' => 'WH-SHOW'],
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_warehouse(): void
    {
        $response = $this->getJson('/api/v1/warehouses/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // PUT /api/v1/warehouses/{id}
    // -------------------------------------------------------------------------

    /** @test */
    public function it_updates_a_warehouse(): void
    {
        $warehouse = Warehouse::create($this->warehouseData(['code' => 'WH-UPD', 'name' => 'Old Name']));

        $response = $this->putJson("/api/v1/warehouses/{$warehouse->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => ['name' => 'Updated Name'],
            ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/v1/warehouses/{id}
    // -------------------------------------------------------------------------

    /** @test */
    public function it_soft_deletes_a_warehouse_with_no_stock(): void
    {
        $warehouse = Warehouse::create($this->warehouseData(['code' => 'WH-DEL']));

        $response = $this->deleteJson("/api/v1/warehouses/{$warehouse->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('warehouses', ['id' => $warehouse->id]);
    }

    // -------------------------------------------------------------------------
    // Bin management
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_and_lists_bins_for_a_warehouse(): void
    {
        $warehouse = Warehouse::create($this->warehouseData(['code' => 'WH-BIN']));

        $createResponse = $this->postJson("/api/v1/warehouses/{$warehouse->id}/bins", [
            'code' => 'BIN-A01',
            'name' => 'Aisle A, Row 01',
            'zone' => 'A',
        ]);

        $createResponse->assertStatus(201);

        $listResponse = $this->getJson("/api/v1/warehouses/{$warehouse->id}/bins");

        $listResponse->assertStatus(200);
        $bins = $listResponse->json('data');
        self::assertCount(1, $bins);
        self::assertSame('BIN-A01', $bins[0]['code']);
    }
}
