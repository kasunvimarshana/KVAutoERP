<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function __construct(private readonly TenantModel $model) {}

    public function findById(int $id): ?Tenant
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $model = $this->model->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByDomain(string $domain): ?Tenant
    {
        $model = $this->model->where('domain', $domain)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): Tenant
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): Tenant
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->where('id', $id)->delete();
    }

    public function all(): Collection
    {
        return $this->model->all()->map(fn (TenantModel $m) => $this->toEntity($m));
    }

    private function toEntity(TenantModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            domain: $model->domain,
            status: $model->status,
            plan: $model->plan,
            settings: $model->settings ?? [],
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
