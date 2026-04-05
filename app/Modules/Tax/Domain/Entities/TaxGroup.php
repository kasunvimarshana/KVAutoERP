<?php

declare(strict_types=1);

namespace Modules\Tax\Domain\Entities;

class TaxGroup
{
    public function __construct(
        private readonly int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly ?string $description,
        private readonly bool $isCompound,
        private readonly bool $isActive,
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isCompound(): bool
    {
        return $this->isCompound;
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
