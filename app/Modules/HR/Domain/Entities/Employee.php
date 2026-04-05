<?php declare(strict_types=1);
namespace Modules\HR\Domain\Entities;
class Employee {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $userId,
        private readonly string $employeeCode,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $email,
        private readonly ?string $phone,
        private readonly int $departmentId,
        private readonly ?int $positionId,
        private readonly \DateTimeInterface $hireDate,
        private readonly string $status,
        private readonly float $basicSalary,
        private readonly string $salaryType,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getUserId(): int { return $this->userId; }
    public function getEmployeeCode(): string { return $this->employeeCode; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return "{$this->firstName} {$this->lastName}"; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getDepartmentId(): int { return $this->departmentId; }
    public function getPositionId(): ?int { return $this->positionId; }
    public function getHireDate(): \DateTimeInterface { return $this->hireDate; }
    public function getStatus(): string { return $this->status; }
    public function getBasicSalary(): float { return $this->basicSalary; }
    public function getSalaryType(): string { return $this->salaryType; }
    public function isActive(): bool { return $this->status === 'active'; }
}
