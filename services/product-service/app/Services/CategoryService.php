<?php
namespace App\Services;
use App\Exceptions\ServiceException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(private readonly CategoryRepositoryInterface $repository) {}

    public function list(string $tenantId, array $params = []): LengthAwarePaginator|Collection
    {
        return $this->repository->all(['tenant_id' => $tenantId], $params);
    }

    public function create(string $tenantId, array $data): Category
    {
        $data['tenant_id'] = $tenantId;
        return $this->repository->create($data);
    }

    public function get(string $id, string $tenantId): Category
    {
        $cat = $this->repository->findById($id);
        if (!$cat || $cat->tenant_id !== $tenantId) {
            throw new ServiceException('Category not found.', 404);
        }
        return $cat->load(['parent', 'children']);
    }

    public function update(string $id, string $tenantId, array $data): Category
    {
        $cat = $this->get($id, $tenantId);
        return $this->repository->update($cat->id, $data);
    }

    public function delete(string $id, string $tenantId): void
    {
        $cat = $this->get($id, $tenantId);
        $this->repository->delete($cat->id);
    }
}
