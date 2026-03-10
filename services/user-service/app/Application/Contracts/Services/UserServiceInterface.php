<?php

declare(strict_types=1);

namespace App\Application\Contracts\Services;

use App\Application\DTOs\UserProfileDTO;
use App\Domain\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function getAllUsers(array $params): LengthAwarePaginator;
    public function getUserProfile(string|int $userId, string|int $tenantId): UserProfile;
    public function createProfile(UserProfileDTO $dto): UserProfile;
    public function updateProfile(string|int $userId, array $data, string|int $tenantId): UserProfile;
    public function deleteProfile(string|int $userId, string|int $tenantId): bool;
    public function assignRole(string|int $userId, string $role, string|int $tenantId): UserProfile;
    public function getUserPermissions(string|int $userId, string|int $tenantId): array;
    public function activateUser(string|int $userId, string|int $tenantId): UserProfile;
    public function deactivateUser(string|int $userId, string|int $tenantId): UserProfile;
}
