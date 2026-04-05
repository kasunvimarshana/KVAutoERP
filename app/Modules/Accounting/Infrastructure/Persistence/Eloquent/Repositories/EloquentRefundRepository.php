<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\Refund;
use Modules\Accounting\Domain\RepositoryInterfaces\RefundRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\RefundModel;

class EloquentRefundRepository implements RefundRepositoryInterface
{
    public function __construct(
        private readonly RefundModel $model,
    ) {}

    public function findById(int $id): ?Refund
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function create(array $data): Refund
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Refund
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    private function toEntity(RefundModel $model): Refund
    {
        return new Refund(
            id: $model->id,
            tenantId: $model->tenant_id,
            referenceNo: $model->reference_no,
            refundDate: $model->refund_date,
            amount: (float) $model->amount,
            currency: $model->currency,
            paymentMethod: $model->payment_method,
            status: $model->status,
            paymentId: $model->payment_id,
            reason: $model->reason,
            accountId: $model->account_id,
            notes: $model->notes,
            createdAt: $model->created_at,
        );
    }
}
