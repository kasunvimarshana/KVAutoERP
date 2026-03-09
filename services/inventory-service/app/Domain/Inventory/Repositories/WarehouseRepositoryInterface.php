<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\Warehouse;
use App\Shared\Contracts\RepositoryInterface;

/**
 * Warehouse repository contract.
 */
interface WarehouseRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a warehouse by its unique code within a tenant.
     */
    public function findByCode(string $code, string $tenantId): ?Warehouse;

    /**
     * Return all WarehouseStock records for a given warehouse.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getWarehouseStock(string $warehouseId): array;
}
