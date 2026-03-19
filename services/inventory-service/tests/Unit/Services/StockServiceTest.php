<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\StockItemRepositoryInterface;
use App\Contracts\Repositories\StockLedgerRepositoryInterface;
use App\Models\StockLedger;
use App\Services\StockService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Unit tests for the StockService.
 *
 * Repository dependencies are mocked so no database is required.
 */
final class StockServiceTest extends TestCase
{
    private StockService $service;

    /** @var MockInterface&StockItemRepositoryInterface */
    private MockInterface $stockItemRepo;

    /** @var MockInterface&StockLedgerRepositoryInterface */
    private MockInterface $ledgerRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockItemRepo = Mockery::mock(StockItemRepositoryInterface::class);
        $this->ledgerRepo    = Mockery::mock(StockLedgerRepositoryInterface::class);

        $this->service = new StockService(
            $this->stockItemRepo,
            $this->ledgerRepo,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_idempotent_result_when_key_already_processed(): void
    {
        $existingLedger = new StockLedger([
            'id'               => 'ledger-uuid-001',
            'transaction_type' => 'receive',
            'qty_change'       => '50.0000',
            'qty_after'        => '50.0000',
        ]);

        $this->ledgerRepo
            ->shouldReceive('findByIdempotencyKey')
            ->with('key-001')
            ->once()
            ->andReturn($existingLedger);

        $result = $this->service->receive([
            'product_id'      => 'b5d3e7f1-1a2b-4c3d-8e4f-5a6b7c8d9e0f',
            'warehouse_id'    => 'a1b2c3d4-1234-4234-8234-1234567890ab',
            'qty'             => 50,
            'idempotency_key' => 'key-001',
        ]);

        self::assertSame($existingLedger, $result);
    }

    /** @test */
    public function it_validates_bcmath_precision(): void
    {
        // Verify that BCMath correctly handles 4 decimal precision.
        $qty1   = '50.0000';
        $qty2   = '30.0000';
        $result = bcadd($qty1, $qty2, 4);

        self::assertSame('80.0000', $result);

        // Verify subtraction.
        $result = bcsub($qty1, '10.0001', 4);
        self::assertSame('39.9999', $result);

        // Verify multiplication (cost calculation).
        $cost  = '10.5000';
        $total = bcmul('100.0000', $cost, 4);
        self::assertSame('1050.0000', $total);
    }
}

