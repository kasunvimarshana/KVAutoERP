<?php declare(strict_types=1);
namespace Modules\HR\Domain\RepositoryInterfaces;
use Modules\HR\Domain\Entities\Employee;
interface EmployeeRepositoryInterface {
    public function findById(int $id): ?Employee;
    public function findByTenant(int $tenantId, ?string $status = null): array;
    public function findByDepartment(int $departmentId): array;
    public function save(Employee $employee): Employee;
    public function delete(int $id): void;
}
