<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Warehouse;
use App\Models\WarehouseBin;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the warehouse application service.
 */
interface WarehouseServiceInterface
{
    /**
     * Return a paginated list of warehouses.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<Warehouse>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a warehouse by UUID or throw NotFoundException.
     *
     * @param  string  $id
     * @return Warehouse
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function findOrFail(string $id): Warehouse;

    /**
     * Create a new warehouse.
     *
     * @param  array<string, mixed>  $data
     * @return Warehouse
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     */
    public function create(array $data): Warehouse;

    /**
     * Update a warehouse.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Warehouse
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function update(string $id, array $data): Warehouse;

    /**
     * Soft-delete a warehouse.
     *
     * @param  string  $id
     * @return void
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     * @throws \KvEnterprise\SharedKernel\Exceptions\DomainException
     */
    public function delete(string $id): void;

    /**
     * Return all bins for a warehouse.
     *
     * @param  string  $warehouseId
     * @return array<int, WarehouseBin>
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function getBins(string $warehouseId): array;

    /**
     * Create a bin within a warehouse.
     *
     * @param  string                $warehouseId
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     * @throws \KvEnterprise\SharedKernel\Exceptions\ValidationException
     */
    public function createBin(string $warehouseId, array $data): WarehouseBin;

    /**
     * Update a bin.
     *
     * @param  string                $warehouseId
     * @param  string                $binId
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function updateBin(string $warehouseId, string $binId, array $data): WarehouseBin;

    /**
     * Delete a bin.
     *
     * @param  string  $warehouseId
     * @param  string  $binId
     * @return void
     *
     * @throws \KvEnterprise\SharedKernel\Exceptions\NotFoundException
     */
    public function deleteBin(string $warehouseId, string $binId): void;
}
