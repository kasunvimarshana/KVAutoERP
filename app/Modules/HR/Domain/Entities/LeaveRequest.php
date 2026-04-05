<?php declare(strict_types=1);
namespace Modules\HR\Domain\Entities;
class LeaveRequest {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $employeeId,
        private readonly string $leaveType,
        private readonly \DateTimeInterface $startDate,
        private readonly \DateTimeInterface $endDate,
        private readonly float $days,
        private readonly string $status,
        private readonly ?string $reason,
        private readonly ?int $approvedBy,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getEmployeeId(): int { return $this->employeeId; }
    public function getLeaveType(): string { return $this->leaveType; }
    public function getStartDate(): \DateTimeInterface { return $this->startDate; }
    public function getEndDate(): \DateTimeInterface { return $this->endDate; }
    public function getDays(): float { return $this->days; }
    public function getStatus(): string { return $this->status; }
    public function getReason(): ?string { return $this->reason; }
    public function getApprovedBy(): ?int { return $this->approvedBy; }
    public function isApproved(): bool { return $this->status === 'approved'; }
}
