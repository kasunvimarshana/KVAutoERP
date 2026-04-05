<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\Entities;

class TaxGroupRate
{
    public function __construct(
        private readonly int $id,
        private readonly int $tenantId,
        private readonly int $taxGroupId,
        private readonly string $name,
        private readonly float $rate,
        private readonly int $order,
        private readonly bool $isCompound,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getTaxGroupId(): int
    {
        return $this->taxGroupId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function isCompound(): bool
    {
        return $this->isCompound;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
