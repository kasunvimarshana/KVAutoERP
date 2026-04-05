<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

final class EloquentTenantRepository implements TenantRepositoryInterface
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

    public function findByDomain(string $domain): ?Tenant
    {
        $record = $this->model->newQuery()->where('domain', $domain)->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function all(): Collection
    {
        return $this->model->newQuery()
            ->get()
            ->map(fn (TenantModel $m) => $this->toEntity($m));
    }

    public function findActive(): Collection
    {
        return $this->model->newQuery()
            ->where('status', Tenant::STATUS_ACTIVE)
            ->get()
            ->map(fn (TenantModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Tenant
    {
        $record = $this->model->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Tenant
    {
        $record = $this->model->newQuery()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

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

    private function toEntity(TenantModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            domain: $model->domain,
            plan: $model->plan,
            status: $model->status,
            settings: $model->settings,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
