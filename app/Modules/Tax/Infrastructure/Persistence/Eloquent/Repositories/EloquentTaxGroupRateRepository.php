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

    public function create(array $data): TaxGroupRate
    {
        $record = $this->model->newInstance();
        $record->fill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function delete(int $id): void
    {
        $this->model->withoutGlobalScopes()->findOrFail($id)->delete();
    }

    public function listForGroup(int $taxGroupId): array
    {
        return $this->model->withoutGlobalScopes()
            ->where('tax_group_id', $taxGroupId)
            ->orderBy('order')
            ->get()
            ->map(fn ($r) => $this->toEntity($r))
            ->all();
    }

    private function toEntity(TaxGroupRateModel $model): TaxGroupRate
    {
        return new TaxGroupRate(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            taxGroupId: (int) $model->tax_group_id,
            name: (string) $model->name,
            rate: (float) $model->rate,
            order: (int) $model->order,
            isCompound: (bool) $model->is_compound,
            createdAt: $model->created_at,
        );
    }
}
