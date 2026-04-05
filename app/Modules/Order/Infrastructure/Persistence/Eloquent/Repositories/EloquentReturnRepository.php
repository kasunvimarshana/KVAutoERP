<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Order\Domain\Entities\OrderReturn;
use Modules\Order\Domain\RepositoryInterfaces\ReturnRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\ReturnModel;

class EloquentReturnRepository implements ReturnRepositoryInterface
{
    public function __construct(
        private readonly ReturnModel $model,
    ) {}

    public function findById(int $id): ?OrderReturn
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);
        return $record ? $this->toEntity($record) : null;
    }

    public function findByOriginalOrder(int $orderId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('original_order_id', $orderId)
            ->get()
            ->map(fn (ReturnModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByStatus(int $tenantId, string $status): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->get()
            ->map(fn (ReturnModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): OrderReturn
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?OrderReturn
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);
        if ($record === null) {
            return null;
        }
        $record->fill($data)->save();
        return $this->toEntity($record->fresh());
    }

    private function toEntity(ReturnModel $model): OrderReturn
    {
        return new OrderReturn(
            id: $model->id,
            tenantId: $model->tenant_id,
            originalOrderId: $model->original_order_id,
            type: $model->type,
            status: $model->status,
            contactId: $model->contact_id,
            warehouseId: $model->warehouse_id,
            reason: $model->reason ?? '',
            restockingFee: (float) $model->restocking_fee,
            creditMemoAmount: $model->credit_memo_amount !== null ? (float) $model->credit_memo_amount : null,
            notes: $model->notes,
            qualityCheck: (bool) $model->quality_check,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
