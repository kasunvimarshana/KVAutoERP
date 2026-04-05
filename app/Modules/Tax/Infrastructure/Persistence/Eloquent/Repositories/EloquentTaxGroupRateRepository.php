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

    public function findById(int $id, int $tenantId): ?TaxGroupRate
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findByGroup(int $taxGroupId, int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('tax_group_id', $taxGroupId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (TaxGroupRateModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): TaxGroupRate
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function delete(int $id, int $tenantId): bool
    {
        return (bool) $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    public function deleteByGroup(int $taxGroupId, int $tenantId): bool
    {
        return (bool) $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tax_group_id', $taxGroupId)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    private function toDomain(TaxGroupRateModel $model): TaxGroupRate
    {
        return new TaxGroupRate(
            id:          $model->id,
            tenantId:    $model->tenant_id,
            taxGroupId:  $model->tax_group_id,
            taxRateId:   $model->tax_rate_id,
            sortOrder:   (int) $model->sort_order,
        );
    }
}
