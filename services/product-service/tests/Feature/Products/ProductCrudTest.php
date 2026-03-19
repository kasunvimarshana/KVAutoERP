<?php

declare(strict_types=1);

namespace Tests\Feature\Products;

use App\Http\Middleware\VerifyJwtMiddleware;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use KvEnterprise\SharedKernel\Http\Middleware\RequirePermissionMiddleware;
use KvEnterprise\SharedKernel\Http\Middleware\TenantContextMiddleware;
use KvEnterprise\SharedKernel\ValueObjects\TenantId;
use Tests\TestCase;

/**
 * Feature tests for the Product CRUD endpoints.
 *
 * All tests bypass JWT and TenantContext middleware; the tenant context
 * is injected directly into the service container so the model global
 * scopes still enforce per-tenant isolation.
 *
 * Database: SQLite in-memory (configured in phpunit.xml).
 */
final class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bind a fixed test tenant so that TenantAwareModel scopes work.
        $this->setTenantContext();

        // Bypass auth and tenant middleware — we control the context above.
        $this->withoutMiddleware([
            VerifyJwtMiddleware::class,
            TenantContextMiddleware::class,
            RequirePermissionMiddleware::class,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/products
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_an_empty_paginated_product_list(): void
    {
        $response = $this->getJson('/api/v1/products');

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
    public function it_returns_only_products_belonging_to_the_current_tenant(): void
    {
        // Create a product for our test tenant.
        $this->createProduct(['sku' => 'SKU-TENANT-A', 'name' => 'Tenant A Product']);

        // Create a product for a different tenant (bypassing global scope).
        Product::withoutGlobalScopes()->create($this->productData([
            'sku'       => 'SKU-TENANT-B',
            'name'      => 'Tenant B Product',
            'tenant_id' => 'b1234567-1234-4234-8234-1234567890ab',
        ]));

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJson(['meta' => ['pagination' => ['total' => 1]]]);

        $data = $response->json('data');
        self::assertCount(1, $data);
        self::assertSame('SKU-TENANT-A', $data[0]['sku']);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/products
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_a_product_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'sku'             => 'SKU-CREATE-001',
            'name'            => 'Widget Pro',
            'type'            => 'physical',
            'organization_id' => self::TEST_ORG_ID,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'sku'  => 'SKU-CREATE-001',
                    'name' => 'Widget Pro',
                    'slug' => 'widget-pro',
                    'type' => 'physical',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku'       => 'SKU-CREATE-001',
            'tenant_id' => self::TEST_TENANT_ID,
        ]);
    }

    /** @test */
    public function it_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'No SKU Product',
            // sku is required
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error'])
            ->assertJsonPath('errors.sku', fn ($errors) => count($errors) > 0);
    }

    /** @test */
    public function it_returns_422_when_type_is_invalid(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'sku'             => 'SKU-BAD-TYPE',
            'name'            => 'Bad Type Product',
            'type'            => 'invalid_type',
            'organization_id' => self::TEST_ORG_ID,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_rejects_duplicate_sku_within_the_same_tenant(): void
    {
        $this->createProduct(['sku' => 'DUPLICATE-SKU', 'name' => 'First Product']);

        $response = $this->postJson('/api/v1/products', [
            'sku'             => 'DUPLICATE-SKU',
            'name'            => 'Second Product',
            'type'            => 'physical',
            'organization_id' => self::TEST_ORG_ID,
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error']);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/products/{id}
    // -------------------------------------------------------------------------

    /** @test */
    public function it_shows_a_product_by_id(): void
    {
        $product = $this->createProduct(['sku' => 'SKU-SHOW', 'name' => 'Shown Product']);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'id'   => $product->id,
                    'sku'  => 'SKU-SHOW',
                    'name' => 'Shown Product',
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_for_unknown_product_id(): void
    {
        $response = $this->getJson('/api/v1/products/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404)
            ->assertJson(['status' => 'error']);
    }

    // -------------------------------------------------------------------------
    // PUT /api/v1/products/{id}
    // -------------------------------------------------------------------------

    /** @test */
    public function it_updates_a_product_and_returns_200(): void
    {
        $product = $this->createProduct(['sku' => 'SKU-UPDATE', 'name' => 'Before Update']);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'After Update',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'name' => 'After Update',
                    'slug' => 'after-update',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id'   => $product->id,
            'name' => 'After Update',
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_a_non_existent_product(): void
    {
        $response = $this->putJson('/api/v1/products/00000000-0000-0000-0000-000000000000', [
            'name' => 'Ghost Update',
        ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/v1/products/{id}
    // -------------------------------------------------------------------------

    /** @test */
    public function it_soft_deletes_a_product_and_returns_204(): void
    {
        $product = $this->createProduct(['sku' => 'SKU-DELETE', 'name' => 'To Delete']);

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_a_non_existent_product(): void
    {
        $response = $this->deleteJson('/api/v1/products/00000000-0000-0000-0000-000000000000');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Prices sub-resource
    // -------------------------------------------------------------------------

    /** @test */
    public function it_adds_a_price_to_a_product(): void
    {
        $product = $this->createProduct(['sku' => 'SKU-PRICE', 'name' => 'Priced Product']);

        $response = $this->postJson("/api/v1/products/{$product->id}/prices", [
            'currency_code' => 'USD',
            'price_type'    => 'base',
            'price'         => '99.99',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'currency_code' => 'USD',
                    'price_type'    => 'base',
                    'price'         => '99.9900',
                ],
            ]);
    }

    /** @test */
    public function it_lists_prices_for_a_product(): void
    {
        $product = $this->createProduct(['sku' => 'SKU-PRICES-LIST', 'name' => 'Multi-Price Product']);

        // Add two prices.
        $this->postJson("/api/v1/products/{$product->id}/prices", [
            'currency_code' => 'USD',
            'price_type'    => 'base',
            'price'         => '10.00',
        ]);
        $this->postJson("/api/v1/products/{$product->id}/prices", [
            'currency_code' => 'EUR',
            'price_type'    => 'selling',
            'price'         => '9.50',
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}/prices");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // -------------------------------------------------------------------------
    // Variants sub-resource
    // -------------------------------------------------------------------------

    /** @test */
    public function it_adds_a_variant_to_a_variant_type_product(): void
    {
        $product = $this->createProduct([
            'sku'  => 'SKU-VARIANT-PARENT',
            'name' => 'Variant Product',
            'type' => 'variant',
        ]);

        $response = $this->postJson("/api/v1/products/{$product->id}/variants", [
            'sku'        => 'SKU-VARIANT-RED-L',
            'name'       => 'Red / Large',
            'attributes' => ['color' => 'red', 'size' => 'L'],
            'is_active'  => true,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('product_variants', [
            'sku'       => 'SKU-VARIANT-RED-L',
            'tenant_id' => self::TEST_TENANT_ID,
        ]);
    }

    /** @test */
    public function it_rejects_variant_on_a_non_variant_type_product(): void
    {
        $product = $this->createProduct([
            'sku'  => 'SKU-PHYSICAL-ONLY',
            'name' => 'Physical Product',
            'type' => 'physical',
        ]);

        $response = $this->postJson("/api/v1/products/{$product->id}/variants", [
            'sku'  => 'SKU-INVALID-VARIANT',
            'name' => 'Should Fail',
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Create a product in the test database within the current tenant scope.
     *
     * @param  array<string, mixed>  $overrides
     * @return Product
     */
    private function createProduct(array $overrides = []): Product
    {
        return Product::withoutGlobalScopes()->create($this->productData($overrides));
    }

    /**
     * Build a complete product attribute array suitable for Product::create().
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function productData(array $overrides = []): array
    {
        $name = $overrides['name'] ?? 'Test Product';

        return array_merge([
            'tenant_id'       => self::TEST_TENANT_ID,
            'organization_id' => self::TEST_ORG_ID,
            'sku'             => 'TEST-SKU-' . uniqid(),
            'name'            => $name,
            'slug'            => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'type'            => 'physical',
            'status'          => 'active',
            'cost_method'     => 'weighted_average',
            'is_serialized'   => false,
            'is_lot_tracked'  => false,
            'is_batch_tracked' => false,
            'has_expiry'      => false,
        ], $overrides);
    }
}
