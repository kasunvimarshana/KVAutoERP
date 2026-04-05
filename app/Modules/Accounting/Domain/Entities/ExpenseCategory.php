<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class ExpenseCategory
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly ?int $parentId,
        private readonly ?int $accountId,
        private readonly ?string $color,
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    public function getColor(): ?string
    {
        return $this->color;
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
