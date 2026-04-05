<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

final class Role
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $slug,
        private readonly array $permissions,
        private readonly string $tenantId,
    ) {}

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getPermissions(): array { return $this->permissions; }
    public function getTenantId(): string { return $this->tenantId; }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true)
            || in_array('*', $this->permissions, true);
    }
}
