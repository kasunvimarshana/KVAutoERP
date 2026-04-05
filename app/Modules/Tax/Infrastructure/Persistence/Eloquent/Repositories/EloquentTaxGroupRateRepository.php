<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Tax\Domain\Entities\TaxGroupRate;
use Modules\Tax\Domain\RepositoryInterfaces\TaxGroupRateRepositoryInterface;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxGroupRateModel;

class EloquentTaxGroupRateRepository implements TaxGroupRateRepositoryInterface
{
    public function __construct(
        private readonly TaxGroupRateModel $model,
    ) {}

    public function findByTaxGroup(int $taxGroupId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tax_group_id', $taxGroupId)
            ->orderBy('priority')
            ->get()
            ->map(fn (TaxGroupRateModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): TaxGroupRate
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?TaxGroupRate
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

    private function toEntity(TaxGroupRateModel $model): TaxGroupRate
    {
        return new TaxGroupRate(
            id: $model->id,
            taxGroupId: $model->tax_group_id,
            name: $model->name,
            rate: (float) $model->rate,
            type: $model->type,
            priority: (int) $model->priority,
            isActive: (bool) $model->is_active,
        );
    }
}
