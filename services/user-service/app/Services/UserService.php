<?php
namespace App\Services;
use App\Exceptions\ServiceException;
use App\Models\UserProfile;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $repository,
    ) {}

    public function list(string $tenantId, array $params = []): LengthAwarePaginator|Collection
    {
        return $this->repository->all(['tenant_id' => $tenantId], $params);
    }

    public function create(string $tenantId, array $data): UserProfile
    {
        $data['tenant_id'] = $tenantId;
        return $this->repository->create($data);
    }

    public function get(string $id, string $tenantId): UserProfile
    {
        $profile = $this->repository->findById($id);
        if (!$profile || $profile->tenant_id !== $tenantId) {
            throw new ServiceException('User profile not found.', 404);
        }
        return $profile;
    }

    public function update(string $id, string $tenantId, array $data): UserProfile
    {
        $profile = $this->get($id, $tenantId);
        return $this->repository->update($profile->id, $data);
    }

    public function delete(string $id, string $tenantId): void
    {
        $profile = $this->get($id, $tenantId);
        $this->repository->delete($profile->id);
    }

    public function updateStatus(string $id, string $tenantId, string $status): UserProfile
    {
        $profile = $this->get($id, $tenantId);
        return $this->repository->update($profile->id, ['metadata' => array_merge($profile->metadata ?? [], ['status' => $status])]);
    }
}
