<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Entities\Payment;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\PaymentModel;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(
        private readonly PaymentModel $model,
    ) {}

    public function findById(int $id): ?Payment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByParty(string $partyType, int $partyId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('party_type', $partyType)
            ->where('party_id', $partyId)
            ->get()
            ->map(fn (PaymentModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Payment
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Payment
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

        return $this->toEntity($record->fresh());
    }

    private function toEntity(PaymentModel $model): Payment
    {
        return new Payment(
            id: $model->id,
            tenantId: $model->tenant_id,
            referenceNo: $model->reference_no,
            paymentDate: $model->payment_date,
            amount: (float) $model->amount,
            currency: $model->currency,
            paymentMethod: $model->payment_method,
            status: $model->status,
            partyType: $model->party_type,
            partyId: $model->party_id,
            accountId: $model->account_id,
            notes: $model->notes,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
