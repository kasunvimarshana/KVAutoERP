<?php declare(strict_types=1);
namespace Modules\Maintenance\Domain\Entities;

class MaintenanceSchedule
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly int $assetId,
        private readonly string $name,
        private readonly string $frequency,
        private readonly int $intervalDays,
        private readonly \DateTimeInterface $nextDueDate,
        private readonly bool $isActive,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getAssetId(): int { return $this->assetId; }
    public function getName(): string { return $this->name; }
    public function getFrequency(): string { return $this->frequency; }
    public function getIntervalDays(): int { return $this->intervalDays; }
    public function getNextDueDate(): \DateTimeInterface { return $this->nextDueDate; }
    public function isActive(): bool { return $this->isActive; }

    public function isDue(): bool
    {
        return $this->isActive && $this->nextDueDate <= new \DateTimeImmutable();
    }

    public function getNextScheduleDate(): \DateTimeImmutable
    {
        $base = \DateTimeImmutable::createFromInterface($this->nextDueDate);
        return $base->modify("+{$this->intervalDays} days");
    }
}
