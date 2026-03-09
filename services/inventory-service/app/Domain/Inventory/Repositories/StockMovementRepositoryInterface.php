<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\StockMovement;
use App\Shared\Contracts\RepositoryInterface;
use DateTimeInterface;

/**
 * StockMovement repository contract.
 */
interface StockMovementRepositoryInterface extends RepositoryInterface
{
    /**
     * Return all movements for a product with optional filters.
     *
     * @param  array<string, mixed>  $filters  e.g. ['type' => 'in', 'from' => '2024-01-01']
     * @return StockMovement[]
     */
    public function findByProduct(string $productId, array $filters = []): array;

    /**
     * Return all movements for a given external reference (e.g., order ID).
     *
     * @return StockMovement[]
     */
    public function findByReference(string $reference): array;

    /**
     * Aggregate movement summary for a product within a date range.
     *
     * @return array{
     *     total_in: int,
     *     total_out: int,
     *     total_adjustments: int,
     *     net_change: int,
     *     movements_count: int,
     * }
     */
    public function getMovementSummary(
        string $productId,
        DateTimeInterface $from,
        DateTimeInterface $to,
    ): array;
}
