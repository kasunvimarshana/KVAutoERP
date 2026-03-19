<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\UserProfile;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Contract for the UserProfile application service.
 */
interface UserProfileServiceInterface
{
    /**
     * Create a new user profile within a tenant.
     *
     * @param  array<string, mixed>  $data
     * @param  string                $tenantId
     * @param  string                $actorId   UUID of the user performing the action.
     * @return UserProfile
     */
    public function createProfile(array $data, string $tenantId, string $actorId): UserProfile;

    /**
     * Update an existing user profile.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @param  string                $actorId
     * @return UserProfile
     */
    public function updateProfile(string $id, array $data, string $actorId): UserProfile;

    /**
     * Deactivate (soft-disable) a user profile.
     *
     * @param  string  $id
     * @param  string  $actorId
     * @return UserProfile
     */
    public function deactivateUser(string $id, string $actorId): UserProfile;

    /**
     * Assign a role to a user profile.
     *
     * @param  string  $userId   UserProfile UUID.
     * @param  string  $roleId   Role UUID.
     * @param  string  $actorId
     * @return void
     */
    public function assignRole(string $userId, string $roleId, string $actorId): void;

    /**
     * Revoke a role from a user profile.
     *
     * @param  string  $userId
     * @param  string  $roleId
     * @param  string  $actorId
     * @return void
     */
    public function revokeRole(string $userId, string $roleId, string $actorId): void;

    /**
     * Build the JWT claims payload for a given auth user + tenant.
     *
     * Called by the Auth Service (via the internal API) when issuing tokens.
     *
     * @param  string  $authUserId  The UUID from the Auth Service user table.
     * @param  string  $tenantId
     * @return array<string, mixed>|null  Null when no profile is found.
     */
    public function getClaimsForAuth(string $authUserId, string $tenantId): ?array;

    /**
     * Find a user profile by UUID.
     *
     * @param  string  $id
     * @return UserProfile|null
     */
    public function getProfile(string $id): ?UserProfile;

    /**
     * Return a paginated list of user profiles for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<UserProfile>
     */
    public function listByTenant(string $tenantId, int $perPage, int $page): LengthAwarePaginator;
}
