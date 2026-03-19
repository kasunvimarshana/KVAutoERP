<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\UserProfile;

interface UserProfileRepositoryInterface
{
    public function findByUserId(string $userId): ?UserProfile;

    public function find(string $id): ?UserProfile;

    public function createOrUpdate(string $userId, array $data): UserProfile;
}
