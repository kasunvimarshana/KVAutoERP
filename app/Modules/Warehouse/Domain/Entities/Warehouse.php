<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Entities;

class Warehouse
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $code,
        private readonly string $type,
        private readonly array $address,
        private readonly bool $isDefault,
        private readonly bool $isActive,
        private readonly ?int $managerId,
        private readonly ?string $notes,
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

    public function getAddress(): array
    {
        return $this->address;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getManagerId(): ?int
    {
        return $this->managerId;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
