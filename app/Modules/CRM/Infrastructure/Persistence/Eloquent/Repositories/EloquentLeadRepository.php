<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\CRM\Domain\Entities\Lead;
use Modules\CRM\Domain\RepositoryInterfaces\LeadRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\LeadModel;

class EloquentLeadRepository implements LeadRepositoryInterface
{
    public function __construct(
        private readonly LeadModel $model,
    ) {}

    public function findById(int $id): ?Lead
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByStatus(int $tenantId, string $status): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->get()
            ->map(fn (LeadModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByAssignee(int $tenantId, int $userId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('assigned_to', $userId)
            ->get()
            ->map(fn (LeadModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Lead
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Lead
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

    private function toEntity(LeadModel $model): Lead
    {
        return new Lead(
            id: $model->id,
            tenantId: $model->tenant_id,
            contactId: $model->contact_id,
            name: $model->name,
            email: $model->email,
            phone: $model->phone,
            source: $model->source,
            status: $model->status,
            value: (float) $model->value,
            currency: $model->currency,
            assignedTo: $model->assigned_to,
            probability: (int) $model->probability,
            expectedCloseDate: $model->expected_close_date?->toDateTime(),
            notes: $model->notes,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
