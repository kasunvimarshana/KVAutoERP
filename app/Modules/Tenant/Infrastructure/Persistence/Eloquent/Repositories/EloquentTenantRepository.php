<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function __construct(private readonly TenantModel $model) {}

    public function findById(string $id): ?Tenant
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $model = $this->model->withoutGlobalScopes()->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): Tenant
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(string $id, array $data): Tenant
    {
        $model = $this->model->withoutGlobalScopes()->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(string $id): bool
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        if (! $model) {
            throw new NotFoundException('Tenant', $id);
        }

        return (bool) $model->delete();
    }

    public function all(): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->get()
            ->map(fn (TenantModel $m) => $this->toEntity($m));
    }

    private function toEntity(TenantModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            plan: $model->plan,
            status: $model->status,
            settings: $model->settings ?? [],
            createdAt: $model->created_at?->toDateTimeImmutable(),
            updatedAt: $model->updated_at?->toDateTimeImmutable(),
        );
    }
}
