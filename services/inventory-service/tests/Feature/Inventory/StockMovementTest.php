<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Http\Middleware\VerifyJwtMiddleware;
use App\Models\StockItem;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use KvEnterprise\SharedKernel\Http\Middleware\RequirePermissionMiddleware;
use KvEnterprise\SharedKernel\Http\Middleware\TenantContextMiddleware;
use Tests\TestCase;

/**
 * Feature tests for stock movement endpoints.
 *
 * Tests ledger-based receive, dispatch, adjust, transfer, and reservation flows.
 * All tests bypass JWT and tenant middleware; tenant context is injected directly.
 */
final class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private Warehouse $warehouse;
    private string $productId = 'b5d3e7f1-1a2b-4c3d-8e4f-5a6b7c8d9e0f';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setTenantContext();

        $this->withoutMiddleware([
            VerifyJwtMiddleware::class,
            TenantContextMiddleware::class,
            RequirePermissionMiddleware::class,
        ]);

        // Create a test warehouse.
        $this->warehouse = Warehouse::create($this->warehouseData(['code' => 'WH-TEST']));
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/stock/receive
    // -------------------------------------------------------------------------

    /** @test */
    public function it_receives_stock_and_creates_a_ledger_entry(): void
    {
        $response = $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 100,
            'unit_cost'    => '10.5000',
            'currency'     => 'USD',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'transaction_type' => 'receive',
                ],
            ]);

        // Verify stock item created.
        $item = StockItem::where('product_id', $this->productId)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        self::assertNotNull($item);
        self::assertSame('100.0000', bcadd((string) $item->qty_on_hand, '0', 4));
    }

    /** @test */
    public function it_accumulates_stock_on_multiple_receives(): void
    {
        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 50,
            'unit_cost'    => '10.0000',
        ]);

        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 30,
            'unit_cost'    => '12.0000',
        ]);

        $item = StockItem::where('product_id', $this->productId)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        self::assertNotNull($item);
        self::assertSame('80.0000', bcadd((string) $item->qty_on_hand, '0', 4));
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/stock/dispatch
    // -------------------------------------------------------------------------

    /** @test */
    public function it_dispatches_stock_and_reduces_on_hand(): void
    {
        // First receive some stock.
        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 100,
            'unit_cost'    => '10.0000',
        ]);

        // Then dispatch some.
        $response = $this->postJson('/api/v1/stock/dispatch', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 40,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'transaction_type' => 'dispatch',
                ],
            ]);

        $item = StockItem::where('product_id', $this->productId)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        self::assertSame('60.0000', bcadd((string) $item->qty_on_hand, '0', 4));
    }

    /** @test */
    public function it_rejects_dispatch_when_insufficient_stock(): void
    {
        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 10,
            'unit_cost'    => '5.0000',
        ]);

        $response = $this->postJson('/api/v1/stock/dispatch', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 100,
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/stock/adjust
    // -------------------------------------------------------------------------

    /** @test */
    public function it_applies_a_positive_adjustment(): void
    {
        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 50,
            'unit_cost'    => '10.0000',
        ]);

        $response = $this->postJson('/api/v1/stock/adjust', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 10,
            'notes'        => 'Surplus correction',
        ]);

        $response->assertStatus(201);

        $item = StockItem::where('product_id', $this->productId)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        self::assertSame('60.0000', bcadd((string) $item->qty_on_hand, '0', 4));
    }

    /** @test */
    public function it_applies_a_negative_adjustment(): void
    {
        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 50,
            'unit_cost'    => '10.0000',
        ]);

        $this->postJson('/api/v1/stock/adjust', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => -5,
            'notes'        => 'Damaged goods write-off',
        ]);

        $item = StockItem::where('product_id', $this->productId)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        self::assertSame('45.0000', bcadd((string) $item->qty_on_hand, '0', 4));
    }

    // -------------------------------------------------------------------------
    // Stock levels query
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_stock_levels_for_a_product(): void
    {
        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 75,
            'unit_cost'    => '8.0000',
        ]);

        $response = $this->getJson("/api/v1/stock/{$this->productId}");

        $response->assertStatus(200);
        $data = $response->json('data');
        self::assertCount(1, $data);
        self::assertSame('75.0000', bcadd((string) $data[0]['qty_on_hand'], '0', 4));
    }

    // -------------------------------------------------------------------------
    // Idempotency
    // -------------------------------------------------------------------------

    /** @test */
    public function it_is_idempotent_when_same_idempotency_key_is_used(): void
    {
        $payload = [
            'product_id'      => $this->productId,
            'warehouse_id'    => $this->warehouse->id,
            'qty'             => 20,
            'unit_cost'       => '10.0000',
            'idempotency_key' => 'unique-receive-key-001',
        ];

        $this->postJson('/api/v1/stock/receive', $payload);
        $this->postJson('/api/v1/stock/receive', $payload);

        $item = StockItem::where('product_id', $this->productId)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();

        // Stock should only have increased once, not twice.
        self::assertSame('20.0000', bcadd((string) $item->qty_on_hand, '0', 4));
    }

    // -------------------------------------------------------------------------
    // Ledger history
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_paginated_ledger_entries(): void
    {
        $this->postJson('/api/v1/stock/receive', [
            'product_id'   => $this->productId,
            'warehouse_id' => $this->warehouse->id,
            'qty'          => 50,
        ]);

        $response = $this->getJson('/api/v1/ledger?product_id=' . $this->productId);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['pagination'],
            ]);

        self::assertGreaterThanOrEqual(1, count($response->json('data')));
    }
}
