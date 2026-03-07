<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use App\Modules\Product\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantId = '550e8400-e29b-41d4-a716-446655440001';

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

    public function test_product_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/products', ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }

    public function test_create_product_without_sku_returns_validation_error(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\AuthenticateWithKeycloak::class)
             ->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);

        app()->instance('tenant_id', $this->tenantId);

        $response = $this->postJson('/api/v1/products', [
            'name'  => 'A product without SKU',
            'price' => 9.99,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)
                 ->assertJsonPath('errors.sku.0', 'The sku field is required.');
    }

    public function test_create_product_with_valid_data_succeeds(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\AuthenticateWithKeycloak::class)
             ->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);

        app()->instance('tenant_id', $this->tenantId);

        $product = new Product();
        $product->forceFill([
            'id'        => 'prod-uuid',
            'tenant_id' => $this->tenantId,
            'sku'       => 'SKU-FEAT-001',
            'name'      => 'Feature Test Product',
            'price'     => 19.99,
            'is_active' => true,
        ]);

        $productRepo = Mockery::mock(ProductRepositoryInterface::class);
        $productRepo->shouldReceive('findBySku')->andReturn(null);
        $productRepo->shouldReceive('create')->andReturn($product);
        app()->instance(ProductRepositoryInterface::class, $productRepo);

        $response = $this->postJson('/api/v1/products', [
            'sku'   => 'SKU-FEAT-001',
            'name'  => 'Feature Test Product',
            'price' => 19.99,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(201)
                 ->assertJsonPath('data.sku', 'SKU-FEAT-001');
    }
}
