<?php

declare(strict_types=1);

namespace Modules\Employee\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Employee\Application\Contracts\UpdateEmployeeServiceInterface;
use Modules\Employee\Application\DTOs\EmployeeData;
use Modules\Employee\Domain\Entities\Employee;
use Modules\Employee\Domain\Exceptions\EmployeeNotFoundException;
use Modules\Employee\Domain\RepositoryInterfaces\EmployeeRepositoryInterface;

class UpdateEmployeeService extends BaseService implements UpdateEmployeeServiceInterface
{
    public function __construct(private readonly EmployeeRepositoryInterface $employeeRepository)
    {
        parent::__construct($employeeRepository);
    }

    protected function handle(array $data): Employee
    {
        $id = (int) ($data['id'] ?? 0);
        $employee = $this->employeeRepository->find($id);

        if (! $employee) {
            throw new EmployeeNotFoundException($id);
        }

        $dto = EmployeeData::fromArray($data);

        if ($employee->getTenantId() !== $dto->tenant_id) {
            throw new EmployeeNotFoundException($id);
        }

        $employee->update(
            userId: $dto->user_id,
            employeeCode: $dto->employee_code,
            orgUnitId: $dto->org_unit_id,
            jobTitle: $dto->job_title,
            hireDate: $dto->hire_date !== null ? new \DateTimeImmutable($dto->hire_date) : null,
            terminationDate: $dto->termination_date !== null ? new \DateTimeImmutable($dto->termination_date) : null,
            metadata: $dto->metadata,
        );

        return $this->employeeRepository->save($employee);
    }
}
