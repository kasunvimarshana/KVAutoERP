<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Modules\Auth\Domain\Models\User;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Tenant\Domain\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the Inventory Products API.
 */
class InventoryProductsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User   $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Test Corp',
            'slug'      => 'test-corp',
            'plan'      => 'pro',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Admin User',
            'email'     => 'admin@test.com',
            'password'  => bcrypt('Password1!'),
            'is_active' => true,
        ]);
    }

    private function actingAsAdmin(): static
    {
        return $this->actingAs($this->admin, 'api')
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id);
    }

    public function test_index_returns_products_list(): void
    {
        Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Widget A',
            'sku'       => 'WIDGET-001',
            'price'     => 19.99,
            'quantity'  => 100,
            'status'    => 'active',
        ]);

        $response = $this->actingAsAdmin()->getJson('/api/v1/inventory/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['items'],
            ]);
    }

    public function test_store_creates_product(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/v1/inventory/products', [
            'name'     => 'Widget B',
            'sku'      => 'WIDGET-002',
            'price'    => 29.99,
            'quantity' => 50,
            'status'   => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['sku' => 'WIDGET-002'])
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('products', ['sku' => 'WIDGET-002']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAsAdmin()->postJson('/api/v1/inventory/products', []);

        $response->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_show_returns_single_product(): void
    {
        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Widget C',
            'sku'       => 'WIDGET-003',
            'price'     => 9.99,
            'quantity'  => 200,
        ]);

        $response = $this->actingAsAdmin()->getJson("/api/v1/inventory/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['sku' => 'WIDGET-003']);
    }

    public function test_update_modifies_product(): void
    {
        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Old Name',
            'sku'       => 'WIDGET-004',
            'price'     => 10.00,
            'quantity'  => 10,
        ]);

        $response = $this->actingAsAdmin()->putJson("/api/v1/inventory/products/{$product->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);
    }

    public function test_destroy_soft_deletes_product(): void
    {
        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Widget E',
            'sku'       => 'WIDGET-005',
            'price'     => 5.00,
            'quantity'  => 5,
        ]);

        $response = $this->actingAsAdmin()->deleteJson("/api/v1/inventory/products/{$product->id}");

        $response->assertStatus(200)->assertJsonFragment(['success' => true]);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_search_returns_matching_products(): void
    {
        Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Blue Widget',
            'sku'       => 'BLUE-001',
            'price'     => 1.00,
            'quantity'  => 1,
        ]);

        Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Red Gadget',
            'sku'       => 'RED-001',
            'price'     => 2.00,
            'quantity'  => 2,
        ]);

        $response = $this->actingAsAdmin()->getJson('/api/v1/inventory/products/search?q=Blue');

        $response->assertStatus(200);
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertEquals('Blue Widget', $items[0]['name']);
    }

    public function test_products_list_supports_pagination(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Product::withoutGlobalScopes()->create([
                'tenant_id' => $this->tenant->id,
                'name'      => "Product {$i}",
                'sku'       => "SKU-{$i}",
                'price'     => 1.00,
                'quantity'  => 1,
            ]);
        }

        $response = $this->actingAsAdmin()->getJson('/api/v1/inventory/products?per_page=5&page=1');

        $response->assertStatus(200);
        $pagination = $response->json('data.pagination');
        $this->assertEquals(20, $pagination['total']);
        $this->assertEquals(5,  $pagination['per_page']);
        $this->assertEquals(4,  $pagination['last_page']);
    }
}
