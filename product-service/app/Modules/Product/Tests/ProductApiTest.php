<?php

namespace App\Modules\Product\Tests;

use App\Modules\Product\Models\Product;
use App\Services\InventoryService;
use App\Services\RabbitMQService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock external services so tests don't need live connections
        $this->mock(RabbitMQService::class, function ($mock): void {
            $mock->shouldReceive('publish')->andReturn(null);
        });

        $this->mock(InventoryService::class, function ($mock): void {
            $mock->shouldReceive('getInventoryByProductName')->andReturn([]);
            $mock->shouldReceive('deleteByProductName')->andReturn(true);
        });
    }

    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Widget',
            'description' => 'A test widget description',
            'price' => 19.99,
            'stock_quantity' => 100,
            'sku' => 'TEST-SKU-001',
        ], $overrides);
    }

    public function test_index_returns_paginated_product_list(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_store_creates_product(): void
    {
        $payload = $this->productPayload();

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', $payload['name'])
            ->assertJsonPath('data.sku', $payload['sku']);

        $this->assertDatabaseHas('products', ['sku' => $payload['sku']]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock_quantity', 'sku']);
    }

    public function test_store_rejects_duplicate_sku(): void
    {
        $payload = $this->productPayload();
        Product::factory()->create(['sku' => $payload['sku']]);

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_show_returns_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.sku', $product->sku)
            ->assertJsonStructure(['data' => ['id', 'name', 'sku', 'price', 'stock_quantity', 'inventory']]);
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/v1/products/99999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 29.99,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.price', '29.99');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Name']);
    }

    public function test_update_returns_404_for_missing_product(): void
    {
        $response = $this->putJson('/api/v1/products/99999', ['name' => 'Ghost']);

        $response->assertStatus(404);
    }

    public function test_destroy_soft_deletes_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_destroy_returns_404_for_missing_product(): void
    {
        $response = $this->deleteJson('/api/v1/products/99999');

        $response->assertStatus(404);
    }
}
