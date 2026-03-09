<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use DateTimeImmutable;

/**
 * User Domain Entity.
 *
 * Pure domain object — no Eloquent, no framework dependencies.
 * Represents an authenticated user within a specific tenant context.
 */
final class User
{
    /**
     * @param  string             $id           UUID primary key.
     * @param  string             $tenantId     Owning tenant UUID.
     * @param  string             $email        Validated email address.
     * @param  string             $name         Display name.
     * @param  array<string>      $roles        Assigned role names.
     * @param  array<string>      $permissions  Directly granted permissions.
     * @param  bool               $isActive     Whether the account is active.
     * @param  DateTimeImmutable  $createdAt    Record creation timestamp.
     * @param  DateTimeImmutable  $updatedAt    Last update timestamp.
     * @param  DateTimeImmutable|null $lastLoginAt Most recent login timestamp.
     */
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $email,
        private readonly string $name,
        private readonly array $roles = [],
        private readonly array $permissions = [],
        private readonly bool $isActive = true,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
        private readonly DateTimeImmutable $updatedAt = new DateTimeImmutable(),
        private readonly ?DateTimeImmutable $lastLoginAt = null,
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // Getters
    // ──────────────────────────────────────────────────────────────────────

    public function getId(): string
    {
        return $this->id;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return array<string> */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /** @return array<string> */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLastLoginAt(): ?DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Domain behaviour
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Determine whether the user holds the given role.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, strict: true);
    }

    /**
     * Determine whether the user holds any of the given roles.
     *
     * @param  array<string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user has the given permission directly or via role.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, strict: true);
    }

    /**
     * Determine whether the user belongs to the specified tenant.
     */
    public function belongsToTenant(string $tenantId): bool
    {
        return $this->tenantId === $tenantId;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Factory
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Hydrate a User entity from a plain associative array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            tenantId: (string) ($data['tenant_id'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            roles: (array) ($data['roles'] ?? []),
            permissions: (array) ($data['permissions'] ?? []),
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: self::parseDate($data['created_at'] ?? null),
            updatedAt: self::parseDate($data['updated_at'] ?? null),
            lastLoginAt: isset($data['last_login_at'])
                ? self::parseDate($data['last_login_at'])
                : null,
        );
    }

    /**
     * Serialise the entity to a plain associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'tenant_id'     => $this->tenantId,
            'email'         => $this->email,
            'name'          => $this->name,
            'roles'         => $this->roles,
            'permissions'   => $this->permissions,
            'is_active'     => $this->isActive,
            'created_at'    => $this->createdAt->format(DateTimeImmutable::ATOM),
            'updated_at'    => $this->updatedAt->format(DateTimeImmutable::ATOM),
            'last_login_at' => $this->lastLoginAt?->format(DateTimeImmutable::ATOM),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private static function parseDate(mixed $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return DateTimeImmutable::createFromMutable($value);
        }

        if (is_string($value) && $value !== '') {
            return new DateTimeImmutable($value);
        }

        return new DateTimeImmutable();
    }
}
