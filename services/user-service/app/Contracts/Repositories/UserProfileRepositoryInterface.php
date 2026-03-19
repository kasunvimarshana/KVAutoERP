<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\UserProfile;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the UserProfile repository.
 */
interface UserProfileRepositoryInterface
{
    /**
     * Find a user profile by its UUID.
     *
     * @param  string  $id
     * @return UserProfile|null
     */
    public function findById(string $id): ?UserProfile;

    /**
     * Find a user profile by the corresponding Auth Service user ID.
     *
     * @param  string  $authUserId
     * @return UserProfile|null
     */
    public function findByAuthUserId(string $authUserId): ?UserProfile;

    /**
     * Find a user profile by email within a specific tenant.
     *
     * @param  string  $email
     * @param  string  $tenantId
     * @return UserProfile|null
     */
    public function findByEmailAndTenant(string $email, string $tenantId): ?UserProfile;

    /**
     * Create a new user profile record.
     *
     * @param  array<string, mixed>  $data
     * @return UserProfile
     */
    public function create(array $data): UserProfile;

    /**
     * Update an existing user profile by UUID.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @return UserProfile
     */
    public function update(string $id, array $data): UserProfile;

    /**
     * Delete a user profile by UUID.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Return a paginated list of user profiles for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<UserProfile>
     */
    public function paginateByTenant(string $tenantId, int $perPage = 20, int $page = 1): LengthAwarePaginator;

    /**
     * Determine whether a user profile with the given email already exists
     * within a tenant, optionally excluding a specific record.
     *
     * @param  string       $email
     * @param  string       $tenantId
     * @param  string|null  $excludeId
     * @return bool
     */
    public function existsByEmailAndTenant(string $email, string $tenantId, ?string $excludeId = null): bool;
}
