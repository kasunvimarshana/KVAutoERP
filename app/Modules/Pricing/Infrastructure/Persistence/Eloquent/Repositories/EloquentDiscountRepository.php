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

    public function findById(int $id, int $tenantId): ?Discount
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findByCode(string $code, int $tenantId): ?Discount
    {
        $record = $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('code', $code)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $this->toDomain($record) : null;
    }

    public function findActive(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->map(fn (DiscountModel $m) => $this->toDomain($m))
            ->all();
    }

    public function allByTenant(int $tenantId): array
    {
        return $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (DiscountModel $m) => $this->toDomain($m))
            ->all();
    }

    public function create(array $data): Discount
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): Discount
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

    public function incrementUsage(int $id): void
    {
        $this->model->newQuery()
            ->withoutGlobalScope('tenant')
            ->where('id', $id)
            ->increment('usage_count');
    }

    private function toDomain(DiscountModel $model): Discount
    {
        return new Discount(
            id:             $model->id,
            tenantId:       $model->tenant_id,
            name:           $model->name,
            code:           $model->code,
            type:           $model->type,
            value:          (float) $model->value,
            appliesToType:  $model->applies_to_type,
            appliesToId:    $model->applies_to_id,
            minOrderAmount: $model->min_order_amount !== null ? (float) $model->min_order_amount : null,
            validFrom:      $model->valid_from
                ? \DateTimeImmutable::createFromInterface($model->valid_from)
                : null,
            validTo:        $model->valid_to
                ? \DateTimeImmutable::createFromInterface($model->valid_to)
                : null,
            isActive:       (bool) $model->is_active,
            usageLimit:     $model->usage_limit,
            usageCount:     (int) $model->usage_count,
            createdAt:      $model->created_at
                ? \DateTimeImmutable::createFromInterface($model->created_at)
                : null,
            updatedAt:      $model->updated_at
                ? \DateTimeImmutable::createFromInterface($model->updated_at)
                : null,
        );
    }
}
