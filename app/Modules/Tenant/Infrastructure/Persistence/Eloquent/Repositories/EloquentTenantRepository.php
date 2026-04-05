<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function __construct(
        private readonly TenantModel $model,
    ) {}

    public function findById(int $id): ?Tenant
    {
        $record = $this->model->newQuery()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $record = $this->model->newQuery()->where('slug', $slug)->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): Tenant
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Tenant
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function all(): array
    {
        return $this->model
            ->newQuery()
            ->get()
            ->map(fn (TenantModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(TenantModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            plan: $model->plan,
            status: $model->status,
            domain: $model->domain,
            settings: $model->settings,
            createdAt: $model->created_at,
        );
    }
}
