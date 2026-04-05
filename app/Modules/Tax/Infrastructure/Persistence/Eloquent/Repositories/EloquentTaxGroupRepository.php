<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Tax\Domain\Entities\TaxGroup;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRepositoryInterface;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupModel;

class EloquentTaxGroupRepository implements TaxGroupRepositoryInterface
{
    public function __construct(
        private readonly TaxGroupModel $model,
    ) {}

    public function findById(int $id): ?TaxGroup
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, string $code): ?TaxGroup
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function all(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (TaxGroupModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): TaxGroup
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?TaxGroup
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

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

    private function toEntity(TaxGroupModel $model): TaxGroup
    {
        return new TaxGroup(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            code: $model->code,
            description: $model->description,
            isCompound: (bool) $model->is_compound,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
