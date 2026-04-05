<?php

declare(strict_types=1);

namespace Modules\Pricing\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Pricing\Domain\Entities\Discount;
use Modules\Pricing\Domain\RepositoryInterfaces\DiscountRepositoryInterface;
use Modules\Pricing\Infrastructure\Persistence\Eloquent\Models\DiscountModel;

class EloquentDiscountRepository implements DiscountRepositoryInterface
{
    public function __construct(
        private readonly DiscountModel $model,
    ) {}

    public function findById(int $id): ?Discount
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByCode(int $tenantId, string $code): ?Discount
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findActive(int $tenantId, \DateTimeInterface $date): array
    {
        $dateStr = $date->format('Y-m-d');

        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $dateStr);
            })
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $dateStr);
            })
            ->get()
            ->map(fn (DiscountModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Discount
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Discount
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

    private function toEntity(DiscountModel $model): Discount
    {
        return new Discount(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            code: $model->code,
            type: $model->type,
            value: (float) $model->value,
            minOrderAmount: $model->min_order_amount !== null ? (float) $model->min_order_amount : null,
            maxUses: $model->max_uses !== null ? (int) $model->max_uses : null,
            usedCount: (int) $model->used_count,
            startDate: $model->start_date?->toDateTime(),
            endDate: $model->end_date?->toDateTime(),
            isActive: (bool) $model->is_active,
            appliesTo: $model->applies_to,
            productIds: $model->product_ids ?? [],
            categoryIds: $model->category_ids ?? [],
            createdAt: $model->created_at,
        );
    }
}
