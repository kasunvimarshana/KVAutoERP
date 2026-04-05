<?php declare(strict_types=1);
namespace Modules\HR\Domain\RepositoryInterfaces;
use Modules\HR\Domain\Entities\Department;
interface DepartmentRepositoryInterface {
    public function findById(int $id): ?Department;
    public function findByTenant(int $tenantId): array;
    public function save(Department $dept): Department;
    public function delete(int $id): void;
}
