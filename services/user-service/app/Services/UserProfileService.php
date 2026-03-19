<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Services\UserProfileServiceInterface;
use App\Models\UserProfile;
use Illuminate\Pagination\LengthAwarePaginator;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;

/**
 * UserProfile application service.
 *
 * Orchestrates user profile CRUD, role assignment, and JWT claims assembly.
 * The key internal method — getClaimsForAuth() — is called by the Auth Service
 * via the internal API to enrich JWT tokens with user-domain data.
 */
final class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $userProfileRepository,
    ) {}

    /**
     * Create a new user profile within a tenant.
     *
     * @param  array<string, mixed>  $data
     * @param  string                $tenantId
     * @param  string                $actorId
     * @return UserProfile
     *
     * @throws ValidationException
     */
    public function createProfile(array $data, string $tenantId, string $actorId): UserProfile
    {
        $email = (string) ($data['email'] ?? '');

        if ($this->userProfileRepository->existsByEmailAndTenant($email, $tenantId)) {
            throw new ValidationException('A user profile with this email already exists in the tenant.', [
                'email' => ['The email has already been taken.'],
            ]);
        }

        $data['tenant_id']  = $tenantId;
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;
        $data['is_active']  = $data['is_active'] ?? true;

        return $this->userProfileRepository->create($data);
    }

    /**
     * Update an existing user profile.
     *
     * @param  string                $id
     * @param  array<string, mixed>  $data
     * @param  string                $actorId
     * @return UserProfile
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateProfile(string $id, array $data, string $actorId): UserProfile
    {
        $profile = $this->userProfileRepository->findById($id);

        if ($profile === null) {
            throw NotFoundException::for('UserProfile', $id);
        }

        // Check email uniqueness if it is being updated.
        if (isset($data['email']) && $data['email'] !== $profile->email) {
            if ($this->userProfileRepository->existsByEmailAndTenant($data['email'], $profile->tenant_id, $id)) {
                throw new ValidationException('A user profile with this email already exists in the tenant.', [
                    'email' => ['The email has already been taken.'],
                ]);
            }
        }

        $data['updated_by'] = $actorId;

        return $this->userProfileRepository->update($id, $data);
    }

    /**
     * Deactivate (soft-disable) a user profile.
     *
     * @param  string  $id
     * @param  string  $actorId
     * @return UserProfile
     *
     * @throws NotFoundException
     */
    public function deactivateUser(string $id, string $actorId): UserProfile
    {
        $profile = $this->userProfileRepository->findById($id);

        if ($profile === null) {
            throw NotFoundException::for('UserProfile', $id);
        }

        return $this->userProfileRepository->update($id, [
            'is_active'  => false,
            'updated_by' => $actorId,
        ]);
    }

    /**
     * Assign a role to a user profile.
     *
     * @param  string  $userId
     * @param  string  $roleId
     * @param  string  $actorId
     * @return void
     *
     * @throws NotFoundException
     */
    public function assignRole(string $userId, string $roleId, string $actorId): void
    {
        $profile = $this->userProfileRepository->findById($userId);

        if ($profile === null) {
            throw NotFoundException::for('UserProfile', $userId);
        }

        $profile->roles()->syncWithoutDetaching([
            $roleId => [
                'tenant_id'  => $profile->tenant_id,
                'granted_by' => $actorId,
                'granted_at' => now(),
            ],
        ]);
    }

    /**
     * Revoke a role from a user profile.
     *
     * @param  string  $userId
     * @param  string  $roleId
     * @param  string  $actorId
     * @return void
     *
     * @throws NotFoundException
     */
    public function revokeRole(string $userId, string $roleId, string $actorId): void
    {
        $profile = $this->userProfileRepository->findById($userId);

        if ($profile === null) {
            throw NotFoundException::for('UserProfile', $userId);
        }

        $profile->roles()->detach($roleId);
    }

    /**
     * Build the JWT claims payload for a given auth_user_id + tenant.
     *
     * Aggregates role slugs, role-permission slugs, and direct (grant-level)
     * permission slugs into a deduplicated permissions array. Returns null
     * when no matching profile is found.
     *
     * @param  string  $authUserId
     * @param  string  $tenantId
     * @return array<string, mixed>|null
     */
    public function getClaimsForAuth(string $authUserId, string $tenantId): ?array
    {
        $profile = $this->userProfileRepository->findByEmailAndTenant('', $tenantId);

        // Use raw query to find by auth_user_id + tenant_id.
        $profile = UserProfile::withoutGlobalScopes()
            ->where('auth_user_id', $authUserId)
            ->where('tenant_id', $tenantId)
            ->with(['roles.permissions', 'directPermissions'])
            ->first();

        if ($profile === null) {
            return null;
        }

        // Collect role slugs.
        $roleSlugs = $profile->roles->pluck('slug')->all();

        // Collect permissions from all roles.
        $rolePermissions = [];
        foreach ($profile->roles as $role) {
            foreach ($role->permissions as $permission) {
                $rolePermissions[] = $permission->slug;
            }
        }

        // Collect direct user permissions (only granted ones).
        $directPermissions = $profile->directPermissions
            ->where('pivot.is_granted', true)
            ->pluck('slug')
            ->all();

        // Deduplicate permission slugs.
        $allPermissions = array_values(array_unique(array_merge($rolePermissions, $directPermissions)));

        return [
            'user_id'         => $profile->auth_user_id,
            'tenant_id'       => $profile->tenant_id,
            'organization_id' => $profile->organization_id,
            'branch_id'       => $profile->branch_id,
            'location_id'     => $profile->location_id,
            'department_id'   => $profile->department_id,
            'roles'           => $roleSlugs,
            'permissions'     => $allPermissions,
            'profile'         => [
                'first_name'   => $profile->first_name,
                'last_name'    => $profile->last_name,
                'display_name' => $profile->display_name ?? $profile->full_name,
                'locale'       => $profile->locale,
                'timezone'     => $profile->timezone,
            ],
        ];
    }

    /**
     * Find a user profile by UUID.
     *
     * @param  string  $id
     * @return UserProfile|null
     */
    public function getProfile(string $id): ?UserProfile
    {
        return $this->userProfileRepository->findById($id);
    }

    /**
     * Return a paginated list of user profiles for a tenant.
     *
     * @param  string  $tenantId
     * @param  int     $perPage
     * @param  int     $page
     * @return LengthAwarePaginator<UserProfile>
     */
    public function listByTenant(string $tenantId, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->userProfileRepository->paginateByTenant($tenantId, $perPage, $page);
    }
}
