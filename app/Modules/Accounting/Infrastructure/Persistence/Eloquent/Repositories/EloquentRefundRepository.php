<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Refund;
use Modules\Accounting\Domain\RepositoryInterfaces\RefundRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\RefundModel;

final class EloquentRefundRepository implements RefundRepositoryInterface
{
    public function __construct(
        private readonly RefundModel $model,
    ) {}

    public function findById(int $id): ?Refund
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByPayment(int $paymentId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('payment_id', $paymentId)
            ->orderBy('refund_date', 'desc')
            ->get()
            ->map(fn (RefundModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Refund
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Refund
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    private function toEntity(RefundModel $model): Refund
    {
        return new Refund(
            id: $model->id,
            tenantId: $model->tenant_id,
            paymentId: $model->payment_id,
            amount: (float) $model->amount,
            refundDate: \DateTimeImmutable::createFromMutable($model->refund_date->toDateTime()),
            reason: $model->reason,
            journalEntryId: $model->journal_entry_id,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
