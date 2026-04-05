<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class TransactionRule
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly array $conditions,
        private readonly string $applyTo,
        private readonly ?int $categoryId,
        private readonly ?int $accountId,
        private readonly int $priority,
        private readonly bool $isActive,
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

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getApplyTo(): string
    {
        return $this->applyTo;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
