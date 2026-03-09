<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Organization\Entities\Organization;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Support\Repository\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    protected string $model = Organization::class;

    public function findById(string $id): ?Organization
    {
        /** @var Organization|null */
        return $this->newQuery()->find($id);
    }

    public function findBySlug(string $tenantId, string $slug): ?Organization
    {
        /** @var Organization|null */
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->first();
    }

    public function findAll(array $filters = []): Collection|LengthAwarePaginator
    {
        return parent::findAll($filters);
    }

    public function findByTenant(string $tenantId, array $filters = []): Collection
    {
        $query = $this->newQuery()->where('tenant_id', $tenantId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('slug', 'LIKE', "%{$filters['search']}%");
            });
        }

        /** @var Collection<int, Organization> */
        return $query->orderBy('name')->get();
    }

    public function findRoots(string $tenantId): Collection
    {
        /** @var Collection<int, Organization> */
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function findChildren(string $parentId): Collection
    {
        /** @var Collection<int, Organization> */
        return $this->newQuery()
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Organization
    {
        /** @var Organization */
        return parent::create($data);
    }

    public function update(string $id, array $data): ?Organization
    {
        /** @var Organization|null */
        return parent::update($id, $data);
    }

    public function delete(string $id): bool
    {
        return parent::delete($id);
    }
}
