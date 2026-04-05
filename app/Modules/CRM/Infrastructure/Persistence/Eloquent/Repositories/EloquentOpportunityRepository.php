<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Opportunity;
use Modules\CRM\Domain\RepositoryInterfaces\OpportunityRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\OpportunityModel;

final class EloquentOpportunityRepository implements OpportunityRepositoryInterface
{
    public function __construct(
        private readonly OpportunityModel $model,
    ) {}

    public function findById(int $id): ?Opportunity
    {
        $r = $this->model->newQueryWithoutScopes()->find($id);
        return $r ? $this->toEntity($r) : null;
    }

    public function findByTenant(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function findByContact(int $contactId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('contact_id', $contactId)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function findByStage(int $tenantId, string $stage): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('stage', $stage)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function save(array $data): Opportunity
    {
        $r = $this->model->newInstance($data);
        $r->save();
        return $this->toEntity($r);
    }

    public function update(int $id, array $data): Opportunity
    {
        $r = $this->model->newQueryWithoutScopes()->findOrFail($id);
        $r->update($data);
        return $this->toEntity($r->fresh());
    }

    public function delete(int $id): void
    {
        $this->model->newQueryWithoutScopes()->findOrFail($id)->delete();
    }

    private function toEntity(OpportunityModel $m): Opportunity
    {
        return new Opportunity(
            id: $m->id,
            tenantId: $m->tenant_id,
            contactId: $m->contact_id,
            name: $m->name,
            stage: $m->stage,
            probability: (int) $m->probability,
            value: (float) $m->value,
            expectedCloseDate: $m->expected_close_date ? new \DateTimeImmutable($m->expected_close_date) : null,
            assignedTo: $m->assigned_to,
            notes: $m->notes,
            lostReason: $m->lost_reason,
            createdAt: new \DateTimeImmutable($m->created_at),
            updatedAt: new \DateTimeImmutable($m->updated_at),
        );
    }
}
