<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Entities;

final class User
{
    public const ROLE_ADMIN    = 'admin';
    public const ROLE_MANAGER  = 'manager';
    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_SUPPLIER = 'supplier';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_EMPLOYEE,
        self::ROLE_CUSTOMER,
        self::ROLE_SUPPLIER,
    ];

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_INACTIVE  = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];

    public function __construct(
        public readonly int $id,
        public readonly ?int $tenantId,
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $emailVerifiedAt,
        public readonly ?string $rememberToken,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->emailVerifiedAt !== null;
    }
}
