<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Repositories\TenantRepositoryInterface;
use Modules\Tenant\Domain\ValueObjects\TenantPlan;
use Modules\Tenant\Domain\ValueObjects\TenantStatus;
use Modules\Tenant\Infrastructure\Persistence\Models\TenantModel;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function __construct(
        private readonly TenantModel $model,
    ) {}

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

    public function findAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (TenantModel $m) => $this->toEntity($m));
    }

    public function findByStatus(TenantStatus $status, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->model
            ->where('status', $status->value)
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (TenantModel $m) => $this->toEntity($m));
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
        $model = $this->model->find($id);

        return $model ? (bool) $model->delete() : false;
    }

    private function toEntity(TenantModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            status: TenantStatus::from($model->status),
            plan: TenantPlan::from($model->plan),
            settings: $model->settings,
            metadata: $model->metadata,
            createdBy: $model->created_by,
            updatedBy: $model->updated_by,
        );
    }
}
