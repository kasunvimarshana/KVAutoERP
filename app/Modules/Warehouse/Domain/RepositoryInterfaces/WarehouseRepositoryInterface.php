<?php declare(strict_types=1);
namespace Modules\Warehouse\Domain\RepositoryInterfaces;
use Modules\Warehouse\Domain\Entities\Warehouse;
interface WarehouseRepositoryInterface {
    public function findById(int $id): ?Warehouse;
    public function findDefault(int $tenantId): ?Warehouse;
    public function findByTenant(int $tenantId): array;
    public function save(Warehouse $warehouse): Warehouse;
    public function delete(int $id): void;
}
