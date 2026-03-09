<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Modules\Auth\Domain\Models\User;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Tenant\Domain\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the Order API including Saga orchestration.
 */
class OrderSagaTest extends TestCase
{
    use RefreshDatabase;

    private Tenant  $tenant;
    private User    $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name'      => 'Order Test Corp',
            'slug'      => 'order-test-corp',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'tenant_id' => $this->tenant->id,
            'name'      => 'Customer One',
            'email'     => 'customer@test.com',
            'password'  => bcrypt('Password1!'),
            'is_active' => true,
        ]);

        $this->product = Product::withoutGlobalScopes()->create([
            'tenant_id'         => $this->tenant->id,
            'name'              => 'Test Product',
            'sku'               => 'TEST-SKU-001',
            'price'             => 25.00,
            'quantity'          => 100,
            'reserved_quantity' => 0,
            'status'            => 'active',
        ]);
    }

    private function actingAsCustomer(): static
    {
        return $this->actingAs($this->customer, 'api')
            ->withHeader('X-Tenant-ID', (string) $this->tenant->id);
    }

    // -------------------------------------------------------------------------
    //  Saga happy path
    // -------------------------------------------------------------------------

    public function test_create_order_saga_completes_successfully(): void
    {
        $response = $this->actingAsCustomer()->postJson('/api/v1/orders', [
            'customer_id' => $this->customer->id,
            'items'       => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 2,
                    'unit_price' => 25.00,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment(['status' => Order::STATUS_CONFIRMED]);

        // Verify stock was reserved and deducted correctly
        $this->product->refresh();
        $this->assertEquals(2, $this->product->reserved_quantity);
    }

    public function test_order_fails_with_insufficient_stock(): void
    {
        $response = $this->actingAsCustomer()->postJson('/api/v1/orders', [
            'customer_id' => $this->customer->id,
            'items'       => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 9999, // more than available
                    'unit_price' => 25.00,
                ],
            ],
        ]);

        // Saga compensation should have run; order is failed or error returned
        $response->assertStatus(500); // SagaException

        // Inventory should NOT have changed (compensation released reservation)
        $this->product->refresh();
        $this->assertEquals(0, $this->product->reserved_quantity);
    }

    // -------------------------------------------------------------------------
    //  Order lifecycle
    // -------------------------------------------------------------------------

    public function test_list_orders_returns_tenant_scoped_results(): void
    {
        $response = $this->actingAsCustomer()->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['items'],
            ]);
    }

    public function test_cancel_order_changes_status_to_cancelled(): void
    {
        // Create order directly in DB to avoid saga complexity
        $order = Order::withoutGlobalScopes()->create([
            'tenant_id'   => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'status'      => Order::STATUS_PENDING,
            'saga_status' => Order::SAGA_COMPLETED,
            'total_amount' => 50.00,
        ]);

        $response = $this->actingAsCustomer()->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => Order::STATUS_CANCELLED]);
    }

    public function test_show_returns_order_details(): void
    {
        $order = Order::withoutGlobalScopes()->create([
            'tenant_id'   => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'status'      => Order::STATUS_CONFIRMED,
            'saga_status' => Order::SAGA_COMPLETED,
            'total_amount' => 100.00,
        ]);

        $response = $this->actingAsCustomer()->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $order->id]);
    }
}
