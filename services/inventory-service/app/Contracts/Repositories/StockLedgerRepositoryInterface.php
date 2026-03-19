<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\StockLedger;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Contract for the stock ledger repository.
 *
 * The ledger is append-only; no update or delete operations are exposed.
 */
interface StockLedgerRepositoryInterface
{
    /**
     * Append a new ledger entry.
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     */
    public function append(array $data): StockLedger;

    /**
     * Return paginated ledger entries with optional filtering.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<StockLedger>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator;

    /**
     * Find a ledger entry by idempotency key (to detect duplicates).
     *
     * @param  string  $idempotencyKey
     * @return StockLedger|null
     */
    public function findByIdempotencyKey(string $idempotencyKey): ?StockLedger;

    /**
     * Return ledger entries for a product ordered by transacted_at ASC.
     * Used for FIFO/FEFO reconstruction.
     *
     * @param  string      $productId
     * @param  string      $warehouseId
     * @param  string|null $binId
     * @return array<int, StockLedger>
     */
    public function getProductHistory(
        string $productId,
        string $warehouseId,
        ?string $binId = null,
    ): array;
}
