<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Payment;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\PaymentModel;

final class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(
        private readonly PaymentModel $model,
    ) {}

    public function findById(int $id): ?Payment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByReference(int $tenantId, string $referenceNo): ?Payment
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('reference_no', $referenceNo)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByPayable(string $payableType, int $payableId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('payable_type', $payableType)
            ->where('payable_id', $payableId)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn (PaymentModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Payment
    {
        $record = $this->model->newQueryWithoutScopes()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Payment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

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

    private function toEntity(PaymentModel $model): Payment
    {
        return new Payment(
            id: $model->id,
            tenantId: $model->tenant_id,
            referenceNo: $model->reference_no,
            date: \DateTimeImmutable::createFromMutable($model->date->toDateTime()),
            amount: (float) $model->amount,
            currencyCode: $model->currency_code,
            paymentMethod: $model->payment_method,
            bankAccountId: $model->bank_account_id,
            journalEntryId: $model->journal_entry_id,
            payableType: $model->payable_type,
            payableId: $model->payable_id,
            notes: $model->notes,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
