<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Warehouse;
use App\Models\WarehouseBin;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;

/**
 * Contract for the warehouse repository.
 */
interface WarehouseRepositoryInterface
{
    /**
     * Return a paginated list of warehouses.
     *
     * @param  int             $page
     * @param  int             $perPage
     * @param  FilterDTO|null  $filter
     * @return LengthAwarePaginator<Warehouse>
     */
    public function paginate(int $page = 1, int $perPage = 15, ?FilterDTO $filter = null): LengthAwarePaginator;

    /**
     * Find a warehouse by UUID.
     *
     * @param  string  $id
     * @return Warehouse|null
     */
    public function findById(string $id): ?Warehouse;

    /**
     * Find a warehouse by code within the current tenant.
     *
     * @param  string  $code
     * @return Warehouse|null
     */
    public function findByCode(string $code): ?Warehouse;

    /**
     * Create a new warehouse.
     *
     * @param  array<string, mixed>  $data
     * @return Warehouse
     */
    public function create(array $data): Warehouse;

    /**
     * Update an existing warehouse.
     *
     * @param  Warehouse             $warehouse
     * @param  array<string, mixed>  $data
     * @return Warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse;

    /**
     * Soft-delete a warehouse.
     *
     * @param  Warehouse  $warehouse
     * @return void
     */
    public function delete(Warehouse $warehouse): void;

    /**
     * Find a bin within a warehouse.
     *
     * @param  string  $warehouseId
     * @param  string  $binId
     * @return WarehouseBin|null
     */
    public function findBin(string $warehouseId, string $binId): ?WarehouseBin;

    /**
     * Return all bins for a warehouse.
     *
     * @param  string  $warehouseId
     * @return array<int, WarehouseBin>
     */
    public function getBins(string $warehouseId): array;

    /**
     * Create a bin within a warehouse.
     *
     * @param  string                $warehouseId
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     */
    public function createBin(string $warehouseId, array $data): WarehouseBin;

    /**
     * Update a warehouse bin.
     *
     * @param  WarehouseBin          $bin
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     */
    public function updateBin(WarehouseBin $bin, array $data): WarehouseBin;

    /**
     * Delete a warehouse bin.
     *
     * @param  WarehouseBin  $bin
     * @return void
     */
    public function deleteBin(WarehouseBin $bin): void;
}
