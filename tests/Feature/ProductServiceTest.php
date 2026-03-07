<?php

namespace Tests\Feature;

use App\Events\ProductCreated;
use App\Events\ProductDeleted;
use App\Events\ProductUpdated;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Feature tests for Service A: Product Service.
 *
 * Verifies CRUD operations, event dispatching, and cross-service
 * data consistency between products and inventory.
 */
class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_can_list_all_products_with_inventories(): void
    {
        $products = Product::factory(3)->create();
        $products->each(fn (Product $p) => Inventory::factory()->create([
            'product_id'   => $p->id,
            'product_name' => $p->name,
        ]));

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'sku', 'inventories'],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_can_create_a_product_and_dispatches_event(): void
    {
        Event::fake([ProductCreated::class]);

        $payload = [
            'name'        => 'Test Widget',
            'description' => 'A test product',
            'price'       => 29.99,
            'sku'         => 'TW-0001',
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Test Widget')
            ->assertJsonPath('data.sku', 'TW-0001');

        $this->assertDatabaseHas('products', ['sku' => 'TW-0001']);

        Event::assertDispatched(ProductCreated::class, function (ProductCreated $event) {
            return $event->product->sku === 'TW-0001';
        });
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'sku']);
    }

    public function test_store_validates_unique_sku(): void
    {
        Product::factory()->create(['sku' => 'DUPLICATE-SKU']);

        $response = $this->postJson('/api/products', [
            'name'  => 'Another Product',
            'price' => 10.00,
            'sku'   => 'DUPLICATE-SKU',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_store_creates_inventory_record_via_event(): void
    {
        $payload = [
            'name'  => 'Event-Driven Product',
            'price' => 49.99,
            'sku'   => 'EDP-001',
        ];

        $this->postJson('/api/products', $payload)->assertCreated();

        // Verify Service B created the inventory record synchronously
        $this->assertDatabaseHas('inventories', [
            'product_name' => 'Event-Driven Product',
            'status'       => 'out_of_stock',
        ]);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_can_show_a_product_with_its_inventories(): void
    {
        $product   = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonCount(1, 'data.inventories');
    }

    public function test_show_returns_404_for_missing_product(): void
    {
        $this->getJson('/api/products/9999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_can_update_a_product_and_dispatches_event(): void
    {
        Event::fake([ProductUpdated::class]);

        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Name']);

        Event::assertDispatched(ProductUpdated::class, fn (ProductUpdated $e) =>
            $e->product->id === $product->id
        );
    }

    public function test_update_syncs_inventory_product_name_via_event(): void
    {
        $product = Product::factory()->create(['name' => 'Original Name']);
        Inventory::factory()->create([
            'product_id'   => $product->id,
            'product_name' => 'Original Name',
        ]);

        $this->putJson("/api/products/{$product->id}", ['name' => 'Updated Name'])
            ->assertOk();

        $this->assertDatabaseHas('inventories', [
            'product_id'   => $product->id,
            'product_name' => 'Updated Name',
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_can_delete_a_product_and_dispatches_event(): void
    {
        Event::fake([ProductDeleted::class]);

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertOk()->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        Event::assertDispatched(ProductDeleted::class, fn (ProductDeleted $e) =>
            $e->productId === $product->id
        );
    }

    public function test_delete_removes_inventory_records_via_event(): void
    {
        $product = Product::factory()->create(['name' => 'Doomed Product']);
        Inventory::factory()->create([
            'product_id'   => $product->id,
            'product_name' => 'Doomed Product',
        ]);

        $this->deleteJson("/api/products/{$product->id}")->assertOk();

        $this->assertDatabaseMissing('inventories', ['product_id' => $product->id]);
    }
}
