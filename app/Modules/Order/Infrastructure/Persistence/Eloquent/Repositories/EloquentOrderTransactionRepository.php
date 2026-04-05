<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Order\Domain\Entities\OrderTransaction;
use Modules\Order\Domain\RepositoryInterfaces\OrderTransactionRepositoryInterface;
use Modules\Order\Infrastructure\Persistence\Eloquent\Models\OrderTransactionModel;

class EloquentOrderTransactionRepository implements OrderTransactionRepositoryInterface
{
    public function __construct(
        private readonly OrderTransactionModel $model,
    ) {}

    public function findByOrder(int $orderId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('order_id', $orderId)
            ->get()
            ->map(fn (OrderTransactionModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): OrderTransaction
    {
        $record = $this->model->newQuery()->create($data);
        return $this->toEntity($record);
    }

    private function toEntity(OrderTransactionModel $model): OrderTransaction
    {
        return new OrderTransaction(
            id: $model->id,
            tenantId: $model->tenant_id,
            orderId: $model->order_id,
            type: $model->type,
            amount: (float) $model->amount,
            currency: $model->currency,
            paymentMethod: $model->payment_method,
            status: $model->status,
            referenceNo: $model->reference_no,
            notes: $model->notes,
            createdAt: $model->created_at,
        );
    }
}
