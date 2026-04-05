<?php declare(strict_types=1);
namespace Modules\HR\Domain\Entities;
class PayrollRecord {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $employeeId,
        private readonly int $payPeriodYear,
        private readonly int $payPeriodMonth,
        private readonly float $basicSalary,
        private readonly float $allowances,
        private readonly float $deductions,
        private readonly float $taxAmount,
        private readonly float $netPay,
        private readonly string $status,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getEmployeeId(): int { return $this->employeeId; }
    public function getPayPeriodYear(): int { return $this->payPeriodYear; }
    public function getPayPeriodMonth(): int { return $this->payPeriodMonth; }
    public function getBasicSalary(): float { return $this->basicSalary; }
    public function getAllowances(): float { return $this->allowances; }
    public function getDeductions(): float { return $this->deductions; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getNetPay(): float { return $this->netPay; }
    public function getStatus(): string { return $this->status; }
}
