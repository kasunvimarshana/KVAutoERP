<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\CRM\Domain\Entities\Opportunity;
use Modules\CRM\Domain\RepositoryInterfaces\OpportunityRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\OpportunityModel;

class EloquentOpportunityRepository implements OpportunityRepositoryInterface
{
    public function __construct(
        private readonly OpportunityModel $model,
    ) {}

    public function findById(int $id): ?Opportunity
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByStage(int $tenantId, string $stage): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('stage', $stage)
            ->get()
            ->map(fn (OpportunityModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findByContact(int $tenantId, int $contactId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('contact_id', $contactId)
            ->get()
            ->map(fn (OpportunityModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Opportunity
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Opportunity
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

    private function toEntity(OpportunityModel $model): Opportunity
    {
        return new Opportunity(
            id: $model->id,
            tenantId: $model->tenant_id,
            leadId: $model->lead_id,
            contactId: $model->contact_id,
            name: $model->name,
            stage: $model->stage,
            value: (float) $model->value,
            currency: $model->currency,
            probability: (int) $model->probability,
            expectedCloseDate: $model->expected_close_date?->toDateTime(),
            assignedTo: $model->assigned_to,
            description: $model->description,
            createdAt: $model->created_at,
        );
    }
}
