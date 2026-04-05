<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Pricing\Domain\Entities\PriceList;
use Modules\Pricing\Domain\RepositoryInterfaces\PriceListRepositoryInterface;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\PriceListModel;

class EloquentPriceListRepository implements PriceListRepositoryInterface
{
    public function __construct(
        private readonly PriceListModel $model,
    ) {}

    public function findById(int $id, int $tenantId): ?PriceList
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findByCode(string $code, int $tenantId): ?PriceList
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('code', $code)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findDefault(int $tenantId): ?PriceList
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (PriceListModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): PriceList
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): PriceList
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

    private function toDomain(PriceListModel $model): PriceList
    {
        return new PriceList(
            id:          $model->id,
            tenantId:    $model->tenant_id,
            name:        $model->name,
            code:        $model->code,
            currency:    $model->currency,
            isDefault:   (bool) $model->is_default,
            validFrom:   $model->valid_from
                ? \DateTimeImmutable::createFromInterface($model->valid_from)
                : null,
            validTo:     $model->valid_to
                ? \DateTimeImmutable::createFromInterface($model->valid_to)
                : null,
            description: $model->description,
            isActive:    (bool) $model->is_active,
            createdAt:   $model->created_at
                ? \DateTimeImmutable::createFromInterface($model->created_at)
                : null,
            updatedAt:   $model->updated_at
                ? \DateTimeImmutable::createFromInterface($model->updated_at)
                : null,
        );
    }
}
