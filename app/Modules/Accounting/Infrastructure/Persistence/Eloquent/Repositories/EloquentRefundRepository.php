<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Accounting\Domain\Entities\Refund;
use Modules\Accounting\Domain\RepositoryInterfaces\RefundRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Models\RefundModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentRefundRepository implements RefundRepositoryInterface
{
    public function __construct(private readonly RefundModel $model) {}

    public function findById(string $id): ?Refund
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        return $m ? $this->toEntity($m) : null;
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)->orderByDesc('refund_date')->get()
            ->map(fn (RefundModel $m) => $this->toEntity($m));
    }

    public function create(array $data): Refund
    {
        return $this->toEntity($this->model->create($data));
    }

    public function delete(string $id): bool
    {
        $m = $this->model->withoutGlobalScopes()->find($id);
        if (! $m) { throw new NotFoundException('Refund', $id); }
        return (bool) $m->delete();
    }

    public function nextRefundNumber(string $tenantId): string
    {
        $count = $this->model->withoutGlobalScopes()->where('tenant_id', $tenantId)->count();
        return 'REF-'.str_pad((string)($count + 1), 6, '0', STR_PAD_LEFT);
    }

    private function toEntity(RefundModel $m): Refund
    {
        return new Refund(
            id: $m->id, tenantId: $m->tenant_id, refundNumber: $m->refund_number,
            refundDate: new \DateTimeImmutable($m->refund_date->toDateString()),
            amount: (float)$m->amount, currency: $m->currency ?? 'USD',
            paymentMethod: $m->payment_method, accountId: $m->account_id,
            reference: $m->reference, notes: $m->notes, status: $m->status,
            originalPaymentId: $m->original_payment_id,
        );
    }
}
