<?php

declare(strict_types=1);

namespace Modules\UserProfile\Application\Services;

use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\UserProfile\Application\Contracts\UserProfileServiceInterface;
use Modules\UserProfile\Domain\Entities\UserProfile;
use Modules\UserProfile\Domain\RepositoryInterfaces\UserProfileRepositoryInterface;

final class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    public function findById(int $id): ?UserProfile
    {
        return $this->profileRepository->findById($id);
    }

    public function findByUserId(int $userId): ?UserProfile
    {
        return $this->profileRepository->findByUserId($userId);
    }

    public function create(array $data): UserProfile
    {
        return $this->profileRepository->create($data);
    }

    public function update(int $id, array $data): ?UserProfile
    {
        $profile = $this->profileRepository->findById($id);

        if ($profile === null) {
            throw new NotFoundException("UserProfile with ID {$id} not found.");
        }

        return $this->profileRepository->update($id, $data);
    }

    public function updateByUserId(int $userId, array $data): ?UserProfile
    {
        $profile = $this->profileRepository->findByUserId($userId);

        if ($profile === null) {
            throw new NotFoundException("UserProfile for user ID {$userId} not found.");
        }

        return $this->profileRepository->updateByUserId($userId, $data);
    }

    public function upsertForUser(int $userId, array $data): UserProfile
    {
        $existing = $this->profileRepository->findByUserId($userId);

        if ($existing !== null) {
            $updated = $this->profileRepository->updateByUserId($userId, $data);

            if ($updated === null) {
                throw new NotFoundException("Failed to update UserProfile for user ID {$userId}.");
            }

            return $updated;
        }

        $data['user_id'] = $userId;

        return $this->profileRepository->create($data);
    }

    public function delete(int $id): bool
    {
        $profile = $this->profileRepository->findById($id);

        if ($profile === null) {
            throw new NotFoundException("UserProfile with ID {$id} not found.");
        }

        return $this->profileRepository->delete($id);
    }

    public function deleteByUserId(int $userId): bool
    {
        return $this->profileRepository->deleteByUserId($userId);
    }
}
