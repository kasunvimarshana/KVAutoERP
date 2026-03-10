<?php
namespace App\Repositories\Contracts;
use App\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserProfileRepositoryInterface
{
    public function all(array $filters = [], array $params = []): LengthAwarePaginator|Collection;
    public function findById(string $id): ?UserProfile;
    public function findByAuthUserId(string $authUserId, string $tenantId): ?UserProfile;
    public function create(array $data): UserProfile;
    public function update(string $id, array $data): UserProfile;
    public function delete(string $id): bool;
}
