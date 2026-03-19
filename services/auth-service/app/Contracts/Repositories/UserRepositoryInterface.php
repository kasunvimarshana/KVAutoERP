<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contract for user persistence operations.
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by their primary key.
     *
     * @param  string  $id  UUID.
     * @return User|null
     */
    public function findById(string $id): ?User;

    /**
     * Find an active user by email within a tenant.
     *
     * @param  string  $email     Email address.
     * @param  string  $tenantId  Tenant UUID.
     * @return User|null
     */
    public function findByEmailAndTenant(string $email, string $tenantId): ?User;

    /**
     * Find a user by email across all tenants (for SSO / super-admin use).
     *
     * @param  string  $email  Email address.
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Persist a new user record.
     *
     * @param  array<string, mixed>  $data  User attributes.
     * @return User
     */
    public function create(array $data): User;

    /**
     * Update an existing user record.
     *
     * @param  string                $id    User UUID.
     * @param  array<string, mixed>  $data  Attributes to update.
     * @return User
     */
    public function update(string $id, array $data): User;

    /**
     * Increment the token_version for a user, invalidating all issued tokens.
     *
     * @param  string  $userId  User UUID.
     * @return int  The new token version value.
     */
    public function incrementTokenVersion(string $userId): int;

    /**
     * Return paginated users scoped to a tenant.
     *
     * @param  string  $tenantId   Tenant UUID.
     * @param  int     $perPage    Items per page.
     * @param  int     $page       Page number.
     * @return LengthAwarePaginator
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator;

    /**
     * Check whether an email is already taken within a tenant.
     *
     * @param  string       $email     Email address.
     * @param  string       $tenantId  Tenant UUID.
     * @param  string|null  $excludeId UUID to exclude (useful on updates).
     * @return bool
     */
    public function existsByEmailAndTenant(string $email, string $tenantId, ?string $excludeId = null): bool;
}
