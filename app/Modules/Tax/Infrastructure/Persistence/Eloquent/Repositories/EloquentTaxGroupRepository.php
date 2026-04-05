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

    public function create(array $data): TaxGroup
    {
        $record = $this->model->newInstance();
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): TaxGroup
    {
        $record = $this->model->withoutGlobalScopes()->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function delete(int $id): void
    {
        $this->model->withoutGlobalScopes()->findOrFail($id)->delete();
    }

    public function findById(int $id, int $tenantId): ?TaxGroup
    {
        $record = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(string $code, int $tenantId): ?TaxGroup
    {
        $record = $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function listAll(int $tenantId): array
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn ($r) => $this->toEntity($r))
            ->all();
    }

    private function toEntity(TaxGroupModel $model): TaxGroup
    {
        return new TaxGroup(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            name: (string) $model->name,
            code: (string) $model->code,
            description: $model->description,
            isCompound: (bool) $model->is_compound,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at,
        );
    }
}
