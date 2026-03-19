<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\DTOs\CreateUserDto;
use App\DTOs\UpdateUserDto;
use App\DTOs\UserProfileDto;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    /**
     * Retrieve a single user by ID, scoped to the given tenant.
     */
    public function getUser(string $userId, string $tenantId): User;

    /**
     * Create a new user under the given tenant.
     */
    public function createUser(CreateUserDto $dto): User;

    /**
     * Update an existing user's data.
     */
    public function updateUser(string $userId, string $tenantId, UpdateUserDto $dto): User;

    /**
     * Soft-delete a user.
     */
    public function deleteUser(string $userId, string $tenantId): void;

    /**
     * Paginate all users for a tenant, with optional filters.
     */
    public function searchUsers(
        string $tenantId,
        array $filters,
        int $perPage = 15,
    ): LengthAwarePaginator;

    /**
     * Retrieve the profile for a given user.
     */
    public function getUserProfile(string $userId, string $tenantId): UserProfile;

    /**
     * Create or update the profile for a given user.
     */
    public function updateUserProfile(string $userId, string $tenantId, UserProfileDto $dto): UserProfile;

    /**
     * Change a user's password after verifying the current password.
     */
    public function changePassword(
        string $userId,
        string $tenantId,
        string $currentPassword,
        string $newPassword,
    ): void;

    /**
     * Activate or deactivate a user.
     */
    public function toggleUserStatus(string $userId, string $tenantId, bool $isActive): User;
}
