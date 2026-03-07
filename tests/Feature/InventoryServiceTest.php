<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Service B: Inventory Service.
 *
 * Verifies inventory listing, filtering, updating, and deletion,
 * as well as cross-service relationship resolution.
 */
class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_can_list_all_inventories(): void
    {
        Inventory::factory(5)->create();

        $response = $this->getJson('/api/inventories');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'product_id', 'product_name', 'quantity', 'status'],
                ],
            ]);
    }

    public function test_can_filter_inventories_by_product_name(): void
    {
        Inventory::factory()->create(['product_name' => 'Alpha Widget']);
        Inventory::factory()->create(['product_name' => 'Beta Gadget']);
        Inventory::factory()->create(['product_name' => 'Alpha Gizmo']);

        $response = $this->getJson('/api/inventories?product_name=Alpha');

        $response->assertOk()->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $item) {
            $this->assertStringContainsStringIgnoringCase('Alpha', $item['product_name']);
        }
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_can_show_an_inventory_record_with_product(): void
    {
        $product   = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
        ]);

        $response = $this->getJson("/api/inventories/{$inventory->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $inventory->id)
            ->assertJsonPath('data.product.id', $product->id);
    }

    public function test_show_returns_404_for_missing_inventory(): void
    {
        $this->getJson('/api/inventories/9999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // Update by ID
    // -------------------------------------------------------------------------

    public function test_can_update_inventory_quantity_and_status(): void
    {
        $inventory = Inventory::factory()->create([
            'quantity' => 0,
            'status'   => 'out_of_stock',
        ]);

        $response = $this->putJson("/api/inventories/{$inventory->id}", [
            'quantity'           => 100,
            'warehouse_location' => 'Main Warehouse',
            'status'             => 'in_stock',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.quantity', 100)
            ->assertJsonPath('data.status', 'in_stock');

        $this->assertDatabaseHas('inventories', [
            'id'       => $inventory->id,
            'quantity' => 100,
            'status'   => 'in_stock',
        ]);
    }

    public function test_update_validates_status_enum(): void
    {
        $inventory = Inventory::factory()->create();

        $response = $this->putJson("/api/inventories/{$inventory->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['status']);
    }

    public function test_update_validates_quantity_is_non_negative(): void
    {
        $inventory = Inventory::factory()->create();

        $response = $this->putJson("/api/inventories/{$inventory->id}", [
            'quantity' => -10,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['quantity']);
    }

    // -------------------------------------------------------------------------
    // Update by product name
    // -------------------------------------------------------------------------

    public function test_can_update_inventories_by_product_name(): void
    {
        Inventory::factory()->create(['product_name' => 'Widget Pro', 'quantity' => 5]);
        Inventory::factory()->create(['product_name' => 'Widget Pro', 'quantity' => 10]);
        Inventory::factory()->create(['product_name' => 'Other Item', 'quantity' => 3]);

        $response = $this->patchJson('/api/inventories/by-product-name', [
            'product_name' => 'Widget Pro',
            'status'       => 'low_stock',
        ]);

        $response->assertOk()
            ->assertJsonPath('records_updated', 2)
            ->assertJsonPath('product_name', 'Widget Pro');

        $this->assertDatabaseHas('inventories', [
            'product_name' => 'Widget Pro',
            'status'       => 'low_stock',
        ]);
        // Unrelated record must remain unchanged
        $this->assertDatabaseHas('inventories', [
            'product_name' => 'Other Item',
            'quantity'     => 3,
        ]);
    }

    public function test_update_by_product_name_requires_product_name(): void
    {
        $response = $this->patchJson('/api/inventories/by-product-name', [
            'quantity' => 50,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['product_name']);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_can_delete_an_inventory_record(): void
    {
        $inventory = Inventory::factory()->create();

        $response = $this->deleteJson("/api/inventories/{$inventory->id}");

        $response->assertOk()->assertJsonPath('message', 'Inventory record deleted successfully.');
        $this->assertDatabaseMissing('inventories', ['id' => $inventory->id]);
    }

    public function test_delete_returns_404_for_missing_inventory(): void
    {
        $this->deleteJson('/api/inventories/9999')->assertNotFound();
    }
}
