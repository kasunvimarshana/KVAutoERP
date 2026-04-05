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

    public function findById(int $id): ?PriceList
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, string $code): ?PriceList
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findDefault(int $tenantId): ?PriceList
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findActive(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->map(fn (PriceListModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): PriceList
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?PriceList
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

    private function toEntity(PriceListModel $model): PriceList
    {
        return new PriceList(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            code: $model->code,
            currency: $model->currency,
            isDefault: (bool) $model->is_default,
            isActive: (bool) $model->is_active,
            startDate: $model->start_date?->toDateTime(),
            endDate: $model->end_date?->toDateTime(),
            description: $model->description,
            createdAt: $model->created_at,
        );
    }
}
