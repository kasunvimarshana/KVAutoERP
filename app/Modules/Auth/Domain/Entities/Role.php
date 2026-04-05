<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

class Role
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $tenantId,
        private readonly string $name,
        private readonly string $slug,
        private readonly ?array $permissions,
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
