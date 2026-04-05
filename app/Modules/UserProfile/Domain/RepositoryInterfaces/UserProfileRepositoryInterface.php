<?php

declare(strict_types=1);

namespace Modules\UserProfile\Domain\RepositoryInterfaces;

use Modules\UserProfile\Domain\Entities\UserProfile;

interface UserProfileRepositoryInterface
{
    public function findById(int $id): ?UserProfile;

    public function findByUserId(int $userId): ?UserProfile;

    public function create(array $data): UserProfile;

    public function update(int $id, array $data): ?UserProfile;

    public function updateByUserId(int $userId, array $data): ?UserProfile;

    public function delete(int $id): bool;

    public function deleteByUserId(int $userId): bool;
}
