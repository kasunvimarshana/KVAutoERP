<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Product\Domain\Entities\UnitOfMeasure;
use Modules\Product\Domain\RepositoryInterfaces\UnitOfMeasureRepositoryInterface;
use Modules\Product\Infrastructure\Persistence\Eloquent\Models\UnitOfMeasureModel;

final class EloquentUnitOfMeasureRepository implements UnitOfMeasureRepositoryInterface
{
    public function __construct(
        private readonly UnitOfMeasureModel $model,
    ) {}

    public function findById(int $id): ?UnitOfMeasure
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByAbbreviation(int $tenantId, string $abbreviation): ?UnitOfMeasure
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('abbreviation', $abbreviation)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByType(int $tenantId, string $type): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->orderBy('name')
            ->get()
            ->map(fn (UnitOfMeasureModel $m) => $this->toEntity($m));
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (UnitOfMeasureModel $m) => $this->toEntity($m));
    }

    public function create(array $data): UnitOfMeasure
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?UnitOfMeasure
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function toEntity(UnitOfMeasureModel $model): UnitOfMeasure
    {
        return new UnitOfMeasure(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            name: (string) $model->name,
            abbreviation: (string) $model->abbreviation,
            type: (string) $model->type,
            baseUnitFactor: (float) $model->base_unit_factor,
            isBaseUnit: (bool) $model->is_base_unit,
            isActive: (bool) $model->is_active,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
