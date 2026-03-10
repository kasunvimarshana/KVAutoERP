<?php
namespace App\Repositories;
use App\Models\UserProfile;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserProfileRepository extends BaseRepository implements UserProfileRepositoryInterface
{
    public function __construct(UserProfile $model) { parent::__construct($model); }

    protected function searchableColumns(): array { return ['first_name', 'last_name', 'phone']; }
    protected function sortableColumns(): array { return ['first_name', 'last_name', 'created_at', 'updated_at']; }

    public function findByAuthUserId(string $authUserId, string $tenantId): ?UserProfile
    {
        return $this->model->where('auth_user_id', $authUserId)->where('tenant_id', $tenantId)->first();
    }
}
