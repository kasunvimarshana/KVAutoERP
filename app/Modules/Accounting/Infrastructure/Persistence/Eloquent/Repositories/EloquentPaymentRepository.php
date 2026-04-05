<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Payment;
use Modules\Accounting\Domain\RepositoryInterfaces\PaymentRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\PaymentModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function __construct(private readonly PaymentModel $model) {}

    public function findById(string $id): ?Payment
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->orderByDesc('payment_date')->get()
            ->map(fn (PaymentModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Payment
    {
        return $this->toEntity($this->model->create($data));
    }

    public function update(string $id, array $data): Payment
    {
        $m = $this->model->withoutGlobalScopes()->findOrFail($id);
        $m->update($data);
        return $this->toEntity($m->fresh());
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('Payment', $id); }
        return (bool) $m->delete();
    }

    public function nextPaymentNumber(string $tenantId): string
    {
        $count = $this->model->withoutGlobalScopes()->where('tenant_id', $tenantId)->count();
        return 'PAY-'.str_pad((string)($count + 1), 6, '0', STR_PAD_LEFT);
    }

    private function toEntity(PaymentModel $m): Payment
    {
        return new Payment(
            id: $m->id, tenantId: $m->tenant_id, paymentNumber: $m->payment_number,
            paymentDate: new \DateTimeImmutable($m->payment_date->toDateString()),
            amount: (float)$m->amount, currency: $m->currency ?? 'USD',
            paymentMethod: $m->payment_method, fromAccountId: $m->from_account_id,
            toAccountId: $m->to_account_id, reference: $m->reference,
            notes: $m->notes, status: $m->status, journalEntryId: $m->journal_entry_id,
        );
    }
}
