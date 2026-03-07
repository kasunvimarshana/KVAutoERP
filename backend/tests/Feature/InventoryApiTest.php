<?php

namespace Tests\Feature;

use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Repositories\InventoryRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantId = '550e8400-e29b-41d4-a716-446655440002';

    protected function setUp(): void
    {
        parent::setUp();
        app()->instance('tenant_id', $this->tenantId);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_inventory_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/inventory', ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }

    public function test_create_inventory_validates_product_id(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\AuthenticateWithKeycloak::class)
             ->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);

        app()->instance('tenant_id', $this->tenantId);

        $response = $this->postJson('/api/v1/inventory', [
            'quantity' => 50,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)
                 ->assertJsonPath('errors.product_id.0', 'The product id field is required.');
    }

    public function test_create_inventory_with_valid_data(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\AuthenticateWithKeycloak::class)
             ->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);

        app()->instance('tenant_id', $this->tenantId);

        $inventory = new Inventory();
        $inventory->forceFill([
            'id'                => 'inv-feat-uuid',
            'tenant_id'         => $this->tenantId,
            'product_id'        => '550e8400-e29b-41d4-a716-446655440099',
            'quantity'          => 100,
            'reserved_quantity' => 0,
            'minimum_quantity'  => 10,
            'status'            => 'in_stock',
        ]);

        $inventoryRepo = Mockery::mock(InventoryRepositoryInterface::class);
        $inventoryRepo->shouldReceive('create')->andReturn($inventory);
        app()->instance(InventoryRepositoryInterface::class, $inventoryRepo);

        $response = $this->postJson('/api/v1/inventory', [
            'product_id' => '550e8400-e29b-41d4-a716-446655440099',
            'quantity'   => 100,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(201)
                 ->assertJsonPath('data.quantity', 100);
    }
}
