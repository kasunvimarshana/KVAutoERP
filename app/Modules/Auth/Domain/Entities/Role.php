<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

final class Role
{
    public const GUARD_API = 'api';
    public const GUARD_WEB = 'web';

    public function __construct(
        public readonly int $id,
        public readonly ?int $tenantId,
        public readonly string $name,
        public readonly string $guardName,
        public readonly ?array $permissions,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
