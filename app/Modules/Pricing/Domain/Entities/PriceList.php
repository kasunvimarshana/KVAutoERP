<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Entities;

class PriceList
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $currency,
        private readonly bool $isDefault,
        private readonly bool $isActive,
        private readonly ?\DateTimeInterface $startDate,
        private readonly ?\DateTimeInterface $endDate,
        private readonly ?string $description,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isValidOn(\DateTimeInterface $date): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->startDate !== null && $date < $this->startDate) {
            return false;
        }

        if ($this->endDate !== null && $date > $this->endDate) {
            return false;
        }

        return true;
    }
}
