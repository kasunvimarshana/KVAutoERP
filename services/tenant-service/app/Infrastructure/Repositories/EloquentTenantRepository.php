<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentTenantRepository
{
    public function __construct(private Tenant $model) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(string $id): ?Tenant
    {
        return $this->model->find($id);
    }

    public function findOrFail(string $id): Tenant
    {
        return $this->model->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function findByDomain(string $domain): ?Tenant
    {
        return $this->model->where('domain', $domain)->first();
    }

    public function create(array $data): Tenant
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Tenant
    {
        $tenant = $this->findOrFail($id);
        $tenant->update($data);
        return $tenant->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    /**
     * Return a paginated result or a plain collection depending on whether
     * `per_page` is present in the params array.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function paginateOrGet(array $params = []): mixed
    {
        $query = $this->model->newQuery();

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['per_page'])) {
            return $query->paginate(
                (int) $params['per_page'],
                ['*'],
                'page',
                (int) ($params['page'] ?? 1)
            );
        }

        return $query->get();
    }
}
