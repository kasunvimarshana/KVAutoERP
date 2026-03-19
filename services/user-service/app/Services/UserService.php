<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\DTOs\CreateUserDto;
use App\DTOs\UpdateUserDto;
use App\DTOs\UserProfileDto;
use App\Exceptions\UserException;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserProfileRepositoryInterface $userProfileRepository,
    ) {}

    public function getUser(string $userId, string $tenantId): User
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw UserException::notFound($userId);
        }

        if ($user->tenant_id !== $tenantId) {
            throw UserException::tenantMismatch();
        }

        return $user;
    }

    public function createUser(CreateUserDto $dto): User
    {
        if ($this->userRepository->existsByEmail($dto->email, $dto->tenantId)) {
            throw UserException::emailAlreadyExists($dto->email);
        }

        return $this->userRepository->create([
            'tenant_id'       => $dto->tenantId,
            'organisation_id' => $dto->organisationId,
            'branch_id'       => $dto->branchId,
            'location_id'     => $dto->locationId,
            'department_id'   => $dto->departmentId,
            'name'            => $dto->name,
            'email'           => $dto->email,
            'password'        => Hash::make($dto->password),
            'phone'           => $dto->phone,
            'avatar'          => $dto->avatar,
            'is_active'       => $dto->isActive,
            'metadata'        => $dto->metadata ?: null,
        ]);
    }

    public function updateUser(string $userId, string $tenantId, UpdateUserDto $dto): User
    {
        $user = $this->getUser($userId, $tenantId);

        $changes = $dto->toArray();

        if (isset($changes['email']) && $changes['email'] !== $user->email) {
            if ($this->userRepository->existsByEmail($changes['email'], $tenantId, $userId)) {
                throw UserException::emailAlreadyExists($changes['email']);
            }
        }

        return $this->userRepository->update($userId, $changes);
    }

    public function deleteUser(string $userId, string $tenantId): void
    {
        $this->getUser($userId, $tenantId);
        $this->userRepository->delete($userId);
    }

    public function searchUsers(
        string $tenantId,
        array $filters,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return $this->userRepository->findAllForTenant($tenantId, $perPage, $filters);
    }

    public function getUserProfile(string $userId, string $tenantId): UserProfile
    {
        $this->getUser($userId, $tenantId);

        $profile = $this->userProfileRepository->findByUserId($userId);

        if ($profile === null) {
            throw UserException::profileNotFound($userId);
        }

        return $profile;
    }

    public function updateUserProfile(
        string $userId,
        string $tenantId,
        UserProfileDto $dto,
    ): UserProfile {
        $user = $this->getUser($userId, $tenantId);

        $data = array_merge(
            $dto->toArray(),
            [
                'user_id'   => $userId,
                'tenant_id' => $user->tenant_id,
            ],
        );

        return $this->userProfileRepository->createOrUpdate($userId, $data);
    }

    public function changePassword(
        string $userId,
        string $tenantId,
        string $currentPassword,
        string $newPassword,
    ): void {
        $user = $this->getUser($userId, $tenantId);

        if (!Hash::check($currentPassword, $user->password)) {
            throw UserException::invalidCurrentPassword();
        }

        $this->userRepository->updatePassword($userId, Hash::make($newPassword));
    }

    public function toggleUserStatus(string $userId, string $tenantId, bool $isActive): User
    {
        $this->getUser($userId, $tenantId);

        $this->userRepository->toggleStatus($userId, $isActive);

        return $this->userRepository->findById($userId);
    }
}
