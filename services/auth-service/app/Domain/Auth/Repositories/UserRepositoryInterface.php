<?php

declare(strict_types=1);

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Entities\User;
use App\Shared\Contracts\RepositoryInterface;

/**
 * User Repository Contract.
 *
 * Extends the platform-wide repository interface with authentication-specific
 * query methods for the Auth bounded context.
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Locate a user by their e-mail address (across all tenants).
     */
    public function findByEmail(string $email): ?User;

    /**
     * Locate a user by tenant + e-mail combination.
     * Returns null when no matching active record exists.
     */
    public function findByTenantAndEmail(string $tenantId, string $email): ?User;

    /**
     * Return all active users belonging to a tenant.
     *
     * @return array<User>
     */
    public function findActiveByTenant(string $tenantId): array;

    /**
     * Assign a named role to a user within the given tenant scope.
     */
    public function assignRole(string $userId, string $role, string $tenantId): void;

    /**
     * Revoke a named role from a user within the given tenant scope.
     */
    public function revokeRole(string $userId, string $role, string $tenantId): void;
}
