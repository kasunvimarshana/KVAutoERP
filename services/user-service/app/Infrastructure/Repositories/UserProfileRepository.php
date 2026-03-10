<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Application\Contracts\Repositories\UserProfileRepositoryInterface;
use App\Domain\Models\UserProfile;
use Shared\BaseRepository\BaseRepository;

class UserProfileRepository extends BaseRepository implements UserProfileRepositoryInterface
{
    protected array $searchableColumns = [
        'phone', 'bio', 'locale', 'timezone',
    ];

    protected array $filterableColumns = [
        'tenant_id', 'is_active', 'locale', 'timezone', 'theme',
    ];

    protected array $sortableColumns = [
        'created_at', 'updated_at', 'user_id', 'is_active',
    ];

    protected array $defaultRelations = ['roles'];

    public function __construct(UserProfile $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(string|int $userId, string|int $tenantId): ?UserProfile
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->with($this->defaultRelations)
            ->first();
    }

    public function findByUserIdOrFail(string|int $userId, string|int $tenantId): UserProfile
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->with($this->defaultRelations)
            ->firstOrFail();
    }

    public function existsByUserId(string|int $userId, string|int $tenantId): bool
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }
}
