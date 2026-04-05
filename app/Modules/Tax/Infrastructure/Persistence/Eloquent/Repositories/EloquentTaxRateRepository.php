<?php

declare(strict_types=1);

namespace Modules\Tax\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Tax\Domain\Entities\TaxRate;
use Modules\Tax\Domain\RepositoryInterfaces\TaxRateRepositoryInterface;
use Modules\Tax\Infrastructure\Persistence\Eloquent\Models\TaxRateModel;

class EloquentTaxRateRepository implements TaxRateRepositoryInterface
{
    public function __construct(
        private readonly TaxRateModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?TaxRate
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findByCode(string $code, int $tenantId): ?TaxRate
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('code', $code)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (TaxRateModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findActive(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->map(fn (TaxRateModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findByCountry(string $country, int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('country', $country)
            ->get()
            ->map(fn (TaxRateModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): TaxRate
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): TaxRate
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->findOrFail($id);

        $record->update($data);

        return $this->toDomain($record->fresh());
    }

    public function delete(int $id, int $tenantId): bool
    {
        return (bool) $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->delete();
    }

    private function toDomain(TaxRateModel $model): TaxRate
    {
        return new TaxRate(
            id:          $model->id,
            tenantId:    $model->tenant_id,
            name:        $model->name,
            code:        $model->code,
            rate:        (float) $model->rate,
            type:        $model->type,
            isCompound:  (bool) $model->is_compound,
            isActive:    (bool) $model->is_active,
            country:     $model->country,
            region:      $model->region,
            description: $model->description,
            createdAt:   $model->created_at
                ? \DateTimeImmutable::createFromInterface($model->created_at)
                : null,
            updatedAt:   $model->updated_at
                ? \DateTimeImmutable::createFromInterface($model->updated_at)
                : null,
        );
    }
}
