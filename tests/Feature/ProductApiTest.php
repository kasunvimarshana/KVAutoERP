<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
                 ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_create_product(): void
    {
        $payload = [
            'name'  => 'Test Product',
            'price' => 9.99,
            'stock' => 100,
            'sku'   => 'SKU-TEST-001',
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertCreated()
                 ->assertJsonPath('data.sku', 'SKU-TEST-001');

        $this->assertDatabaseHas('products', ['sku' => 'SKU-TEST-001']);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->postJson('/api/products', []);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['name', 'price', 'stock', 'sku']);
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
                 ->assertJsonPath('data.id', $product->id);
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $this->getJson('/api/products/9999')->assertNotFound();
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/products/{$product->id}", ['name' => 'New Name']);

        $response->assertOk()
                 ->assertJsonPath('data.name', 'New Name');
    }

    public function test_update_returns_404_for_missing_product(): void
    {
        $this->putJson('/api/products/9999', ['name' => 'X'])->assertNotFound();
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")->assertOk();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_delete_returns_404_for_missing_product(): void
    {
        $this->deleteJson('/api/products/9999')->assertNotFound();
    }
}
