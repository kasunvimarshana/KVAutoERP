<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\WarehouseRepositoryInterface;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Eloquent-backed warehouse repository.
 *
 * All queries are automatically tenant-scoped via TenantAwareModel's
 * global scope on the Warehouse model.
 */
final class WarehouseRepository implements WarehouseRepositoryInterface
{
    /**
     * Return a paginated list of warehouses, optionally filtered.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<Warehouse>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator
    {
        $query = Warehouse::query();

        if ($filter !== null) {
            if ($filter->search !== null && $filter->search !== '') {
                $search = $filter->search;
                $query->where(static function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            foreach (['status', 'type'] as $column) {
                if (isset($filter->filters[$column]) && $filter->filters[$column] !== '') {
                    $query->where($column, $filter->filters[$column]);
                }
            }

            foreach ($filter->sorts as $sort) {
                $query->orderBy($sort['field'], $sort['direction']);
            }
        }

        if ($query->getQuery()->orders === null) {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a warehouse by UUID.
     *
     * @param  string  $id
     * @return Warehouse|null
     */
    public function findById(string $id): ?Warehouse
    {
        return Warehouse::find($id);
    }

    /**
     * Find a warehouse by code within the current tenant.
     *
     * @param  string  $code
     * @return Warehouse|null
     */
    public function findByCode(string $code): ?Warehouse
    {
        return Warehouse::where('code', $code)->first();
    }

    /**
     * Create a new warehouse.
     *
     * @param  array<string, mixed>  $data
     * @return Warehouse
     */
    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    /**
     * Update a warehouse.
     *
     * @param  Warehouse             $warehouse
     * @param  array<string, mixed>  $data
     * @return Warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse->fresh() ?? $warehouse;
    }

    /**
     * Soft-delete a warehouse.
     *
     * @param  Warehouse  $warehouse
     * @return void
     */
    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    /**
     * Find a bin within a warehouse.
     *
     * @param  string  $warehouseId
     * @param  string  $binId
     * @return WarehouseBin|null
     */
    public function findBin(string $warehouseId, string $binId): ?WarehouseBin
    {
        return WarehouseBin::where('warehouse_id', $warehouseId)
            ->where('id', $binId)
            ->first();
    }

    /**
     * Return all bins for a warehouse.
     *
     * @param  string  $warehouseId
     * @return array<int, WarehouseBin>
     */
    public function getBins(string $warehouseId): array
    {
        return WarehouseBin::where('warehouse_id', $warehouseId)
            ->orderBy('code')
            ->get()
            ->all();
    }

    /**
     * Create a bin within a warehouse.
     *
     * @param  string                $warehouseId
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     */
    public function createBin(string $warehouseId, array $data): WarehouseBin
    {
        $data['warehouse_id'] = $warehouseId;

        return WarehouseBin::create($data);
    }

    /**
     * Update a warehouse bin.
     *
     * @param  WarehouseBin          $bin
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     */
    public function updateBin(WarehouseBin $bin, array $data): WarehouseBin
    {
        $bin->update($data);

        return $bin->fresh() ?? $bin;
    }

    /**
     * Delete a warehouse bin.
     *
     * @param  WarehouseBin  $bin
     * @return void
     */
    public function deleteBin(WarehouseBin $bin): void
    {
        $bin->delete();
    }
}
