<?php

declare(strict_types=1);

namespace App\Application\Contracts\Repositories;

use App\Domain\Models\UserProfile;
use Shared\BaseRepository\BaseRepositoryInterface;

interface UserProfileRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(string|int $userId, string|int $tenantId): ?UserProfile;
    public function findByUserIdOrFail(string|int $userId, string|int $tenantId): UserProfile;
    public function existsByUserId(string|int $userId, string|int $tenantId): bool;
}
