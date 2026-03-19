<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\StockLedgerRepositoryInterface;
use App\Models\StockLedger;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Eloquent-backed stock ledger repository.
 *
 * The ledger is append-only — no mutations after creation.
 * All reads are tenant-scoped via TenantAwareModel's global scope.
 */
final class StockLedgerRepository implements StockLedgerRepositoryInterface
{
    /**
     * Append a new ledger entry.
     *
     * @param  array<string, mixed>  $data
     * @return StockLedger
     */
    public function append(array $data): StockLedger
    {
        return StockLedger::create($data);
    }

    /**
     * Return paginated ledger entries.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<StockLedger>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator
    {
        $query = StockLedger::query();

        if ($filter !== null) {
            foreach (['product_id', 'warehouse_id', 'transaction_type', 'reference_type', 'reference_id'] as $column) {
                if (isset($filter->filters[$column]) && $filter->filters[$column] !== '') {
                    $query->where($column, $filter->filters[$column]);
                }
            }

            if (!empty($filter->filters['from_date'])) {
                $query->where('transacted_at', '>=', $filter->filters['from_date']);
            }

            if (!empty($filter->filters['to_date'])) {
                $query->where('transacted_at', '<=', $filter->filters['to_date']);
            }

            foreach ($filter->sorts as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        }

        if ($query->getQuery()->orders === null) {
            $query->orderBy('transacted_at', 'desc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a ledger entry by idempotency key.
     *
     * @param  string  $idempotencyKey
     * @return StockLedger|null
     */
    public function findByIdempotencyKey(string $idempotencyKey): ?StockLedger
    {
        return StockLedger::where('idempotency_key', $idempotencyKey)->first();
    }

    /**
     * Return ledger entries for a product ordered by transacted_at ASC.
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
    ): array {
        $query = StockLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId);

        if ($binId !== null) {
            $query->where('bin_id', $binId);
        }

        return $query->orderBy('transacted_at', 'asc')
            ->get()
            ->all();
    }
}
