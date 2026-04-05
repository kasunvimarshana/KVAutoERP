<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Entities;

class Discount
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $type,
        private readonly float $value,
        private readonly ?float $minOrderAmount,
        private readonly ?int $maxUses,
        private readonly int $usedCount,
        private readonly ?\DateTimeInterface $startDate,
        private readonly ?\DateTimeInterface $endDate,
        private readonly bool $isActive,
        private readonly string $appliesTo,
        private readonly array $productIds,
        private readonly array $categoryIds,
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getMinOrderAmount(): ?float
    {
        return $this->minOrderAmount;
    }

    public function getMaxUses(): ?int
    {
        return $this->maxUses;
    }

    public function getUsedCount(): int
    {
        return $this->usedCount;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getAppliesTo(): string
    {
        return $this->appliesTo;
    }

    public function getProductIds(): array
    {
        return $this->productIds;
    }

    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isValid(\DateTimeInterface $now): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->startDate !== null && $now < $this->startDate) {
            return false;
        }

        if ($this->endDate !== null && $now > $this->endDate) {
            return false;
        }

        if ($this->maxUses !== null && $this->usedCount >= $this->maxUses) {
            return false;
        }

        return true;
    }
}
