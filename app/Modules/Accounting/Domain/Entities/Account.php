<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

class Account
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $code,
        private readonly string $name,
        private readonly string $type,
        private readonly string $normalBalance,
        private readonly ?int $parentId,
        private readonly bool $isActive,
        private readonly ?string $description,
        private readonly array $metadata,
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getNormalBalance(): string
    {
        return $this->normalBalance;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
