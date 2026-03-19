<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Models\UserProfile;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent-backed user profile repository.
 *
 * All queries are automatically tenant-scoped via TenantAwareModel's
 * global scope. Methods that query across tenant boundaries explicitly
 * use withoutGlobalScopes() for cross-tenant admin operations.
 */
final class UserProfileRepository implements UserProfileRepositoryInterface
{
    /**
     * Find a user profile by its UUID (tenant-scoped).
     *
     * @param  string  $id
     * @return UserProfile|null
     */
    public function findById(string $id): ?UserProfile
    {
        return UserProfile::with(['roles.permissions', 'directPermissions'])
            ->find($id);
    }

    /**
     * Find a user profile by auth_user_id, scoped to the current tenant.
     *
     * Uses withoutGlobalScopes to allow cross-tenant lookup when needed
     * (e.g., internal claims endpoint queries by tenant_id explicitly).
     *
     * @param  string  $authUserId
     * @return UserProfile|null
     */
    public function findByAuthUserId(string $authUserId): ?UserProfile
    {
        return UserProfile::with(['roles.permissions', 'directPermissions'])
            ->where('auth_user_id', $authUserId)
            ->first();
    }

    /**
     * Find a user profile by email and tenant ID (bypasses global scope).
     *
     * @param  string  $email
     * @param  string  $tenantId
     * @return UserProfile|null
     */
    public function findByEmailAndTenant(string $email, string $tenantId): ?UserProfile
    {
        return UserProfile::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('email', $email)
            ->with(['roles.permissions', 'directPermissions'])
            ->first();
    }

    /**
     * Create a new user profile record.
     *
     * @param  array<string, mixed>  $data
     * @return UserProfile
     */
    public function create(array $data): UserProfile
    {
        return UserProfile::create($data);
    }

    /**
     * Update an existing user profile by UUID.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return UserProfile
     */
    public function update(string $id, array $data): UserProfile
    {
        $profile = UserProfile::findOrFail($id);
        $profile->update($data);

        return $profile->fresh(['roles.permissions', 'directPermissions']) ?? $profile;
    }

    /**
     * Delete a user profile by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $profile = UserProfile::find($id);

        if ($profile === null) {
            return false;
        }

        return (bool) $profile->delete();
    }

    /**
     * Return a paginated list of user profiles for a specific tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<UserProfile>
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return UserProfile::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['roles'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Check whether a user profile with the given email already exists.
     *
     * @param  string       $email
     * @param  string       $tenantId
     * @param  string|null  $excludeId
     * @return bool
     */
    public function existsByEmailAndTenant(string $email, string $tenantId, ?string $excludeId = null): bool
    {
        $query = UserProfile::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('email', $email);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
