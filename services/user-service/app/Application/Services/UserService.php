<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Application\Contracts\Services\UserServiceInterface;
use App\Application\DTOs\UserProfileDTO;
use App\Domain\Models\Role;
use App\Domain\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    public function getAllUsers(array $params): LengthAwarePaginator
    {
        $tenantId = $params['tenant_id'] ?? null;
        $conditions = $tenantId ? ['tenant_id' => $tenantId] : [];

        return $this->profileRepository->paginate(
            params: $params,
            additionalConditions: $conditions,
        );
    }

    public function getUserProfile(string|int $userId, string|int $tenantId): UserProfile
    {
        return $this->profileRepository->findByUserIdOrFail($userId, $tenantId);
    }

    public function createProfile(UserProfileDTO $dto): UserProfile
    {
        $profile = $this->profileRepository->create([
            'user_id' => $dto->userId,
            'tenant_id' => $dto->tenantId,
            'avatar' => $dto->avatar,
            'bio' => $dto->bio,
            'phone' => $dto->phone,
            'address' => $dto->address,
            'preferences' => $dto->preferences,
            'notification_settings' => $dto->notificationSettings,
            'timezone' => $dto->timezone,
            'locale' => $dto->locale,
            'theme' => $dto->theme,
            'extra_permissions' => $dto->extraPermissions,
            'is_active' => $dto->isActive,
            'metadata' => $dto->metadata,
        ]);

        Log::info('User profile created', ['user_id' => $dto->userId, 'tenant_id' => $dto->tenantId]);

        return $profile->load('roles');
    }

    public function updateProfile(string|int $userId, array $data, string|int $tenantId): UserProfile
    {
        $profile = $this->profileRepository->findByUserIdOrFail($userId, $tenantId);

        $updated = $this->profileRepository->update($profile->id, $data);

        Log::info('User profile updated', ['user_id' => $userId, 'tenant_id' => $tenantId]);

        return $updated->load('roles');
    }

    public function deleteProfile(string|int $userId, string|int $tenantId): bool
    {
        $profile = $this->profileRepository->findByUserIdOrFail($userId, $tenantId);
        $result = $this->profileRepository->delete($profile->id);

        Log::info('User profile deleted', ['user_id' => $userId, 'tenant_id' => $tenantId]);

        return $result;
    }

    public function assignRole(string|int $userId, string $role, string|int $tenantId): UserProfile
    {
        $profile = $this->profileRepository->findByUserIdOrFail($userId, $tenantId);

        $roleModel = Role::where('slug', $role)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $profile->roles()->syncWithoutDetaching([$roleModel->id]);

        Log::info('Role assigned', ['user_id' => $userId, 'role' => $role, 'tenant_id' => $tenantId]);

        return $profile->load('roles');
    }

    public function getUserPermissions(string|int $userId, string|int $tenantId): array
    {
        $profile = $this->profileRepository->findByUserIdOrFail($userId, $tenantId);
        $profile->load('roles');

        // Collect role-based permissions
        $rolePermissions = $profile->roles
            ->flatMap(fn($role) => $role->permissions ?? [])
            ->unique()
            ->values()
            ->toArray();

        // Merge with individual ABAC permissions
        $extraPermissions = $profile->extra_permissions ?? [];

        return array_values(array_unique(array_merge($rolePermissions, $extraPermissions)));
    }

    public function activateUser(string|int $userId, string|int $tenantId): UserProfile
    {
        $profile = $this->profileRepository->findByUserIdOrFail($userId, $tenantId);
        $updated = $this->profileRepository->update($profile->id, ['is_active' => true]);

        Log::info('User activated', ['user_id' => $userId]);

        return $updated;
    }

    public function deactivateUser(string|int $userId, string|int $tenantId): UserProfile
    {
        $profile = $this->profileRepository->findByUserIdOrFail($userId, $tenantId);
        $updated = $this->profileRepository->update($profile->id, ['is_active' => false]);

        Log::info('User deactivated', ['user_id' => $userId]);

        return $updated;
    }
}
