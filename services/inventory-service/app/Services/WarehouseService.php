<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\WarehouseRepositoryInterface;
use App\Contracts\Services\WarehouseServiceInterface;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\DTOs\FilterDTO;
use KvEnterprise\SharedKernel\Exceptions\DomainException;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;

/**
 * Warehouse application service.
 *
 * Orchestrates warehouse and bin CRUD operations with validation,
 * uniqueness checks, and tenant-scoped access control.
 */
final class WarehouseService implements WarehouseServiceInterface
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $warehouseRepository,
    ) {}

    /**
     * Return a paginated list of warehouses.
     *
     * @param  array<string, mixed>  $filters
     * @param  int                   $page
     * @param  int                   $perPage
     * @return LengthAwarePaginator<Warehouse>
     */
    public function list(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $sorts = [];
        if (!empty($filters['sort_by'])) {
            $sorts[] = [
                'field'     => $filters['sort_by'],
                'direction' => strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
            ];
        }

        $filterDTO = new FilterDTO(
            filters: array_filter([
                'status' => $filters['status'] ?? null,
                'type'   => $filters['type'] ?? null,
            ], static fn ($v) => $v !== null && $v !== ''),
            sorts:  $sorts,
            search: $filters['search'] ?? null,
        );

        return $this->warehouseRepository->paginate($page, $perPage, $filterDTO);
    }

    /**
     * Find a warehouse by UUID or throw NotFoundException.
     *
     * @param  string  $id
     * @return Warehouse
     *
     * @throws NotFoundException
     */
    public function findOrFail(string $id): Warehouse
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if ($warehouse === null) {
            throw NotFoundException::for('Warehouse', $id);
        }

        return $warehouse;
    }

    /**
     * Create a new warehouse.
     *
     * @param  array<string, mixed>  $data
     * @return Warehouse
     *
     * @throws ValidationException
     */
    public function create(array $data): Warehouse
    {
        $this->assertCodeUnique($data['code'] ?? '');

        $data['status'] = $data['status'] ?? 'active';
        $data['type']   = $data['type'] ?? 'standard';

        // Ensure only one default warehouse per tenant.
        if (!empty($data['is_default']) && $data['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        return $this->warehouseRepository->create($data);
    }

    /**
     * Update a warehouse.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return Warehouse
     *
     * @throws NotFoundException
     */
    public function update(string $id, array $data): Warehouse
    {
        $warehouse = $this->findOrFail($id);

        if (isset($data['code']) && $data['code'] !== $warehouse->code) {
            $this->assertCodeUnique($data['code']);
        }

        if (!empty($data['is_default']) && $data['is_default'] && !$warehouse->is_default) {
            Warehouse::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
        }

        return $this->warehouseRepository->update($warehouse, $data);
    }

    /**
     * Soft-delete a warehouse.
     *
     * @param  string  $id
     * @return void
     *
     * @throws NotFoundException
     * @throws DomainException
     */
    public function delete(string $id): void
    {
        $warehouse = $this->findOrFail($id);

        // Prevent deletion if the warehouse has active stock.
        $hasStock = $warehouse->stockItems()->where('qty_on_hand', '>', 0)->exists();
        if ($hasStock) {
            throw new DomainException('Cannot delete a warehouse that has active stock on hand.');
        }

        $this->warehouseRepository->delete($warehouse);
    }

    /**
     * Return all bins for a warehouse.
     *
     * @param  string  $warehouseId
     * @return array<int, WarehouseBin>
     *
     * @throws NotFoundException
     */
    public function getBins(string $warehouseId): array
    {
        $this->findOrFail($warehouseId);

        return $this->warehouseRepository->getBins($warehouseId);
    }

    /**
     * Create a bin within a warehouse.
     *
     * @param  string                $warehouseId
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function createBin(string $warehouseId, array $data): WarehouseBin
    {
        $this->findOrFail($warehouseId);

        $data['tenant_id'] = app(\KvEnterprise\SharedKernel\ValueObjects\TenantId::class)->getValue();
        $data['type']      = $data['type'] ?? 'standard';
        $data['status']    = $data['status'] ?? 'active';

        return $this->warehouseRepository->createBin($warehouseId, $data);
    }

    /**
     * Update a bin.
     *
     * @param  string                $warehouseId
     * @param  string                $binId
     * @param  array<string, mixed>  $data
     * @return WarehouseBin
     *
     * @throws NotFoundException
     */
    public function updateBin(string $warehouseId, string $binId, array $data): WarehouseBin
    {
        $this->findOrFail($warehouseId);

        $bin = $this->warehouseRepository->findBin($warehouseId, $binId);
        if ($bin === null) {
            throw NotFoundException::for('WarehouseBin', $binId);
        }

        return $this->warehouseRepository->updateBin($bin, $data);
    }

    /**
     * Delete a bin.
     *
     * @param  string  $warehouseId
     * @param  string  $binId
     * @return void
     *
     * @throws NotFoundException
     */
    public function deleteBin(string $warehouseId, string $binId): void
    {
        $this->findOrFail($warehouseId);

        $bin = $this->warehouseRepository->findBin($warehouseId, $binId);
        if ($bin === null) {
            throw NotFoundException::for('WarehouseBin', $binId);
        }

        $this->warehouseRepository->deleteBin($bin);
    }

    /**
     * Assert that a warehouse code is unique within the current tenant.
     *
     * @param  string  $code
     * @return void
     *
     * @throws ValidationException
     */
    private function assertCodeUnique(string $code): void
    {
        if ($code === '') {
            return;
        }

        if ($this->warehouseRepository->findByCode($code) !== null) {
            throw new ValidationException(['code' => ['Warehouse code already exists for this tenant.']]);
        }
    }
}
