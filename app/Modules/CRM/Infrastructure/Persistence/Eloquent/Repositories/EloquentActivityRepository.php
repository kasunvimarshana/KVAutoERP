<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Activity;
use Modules\CRM\Domain\RepositoryInterfaces\ActivityRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ActivityModel;

final class EloquentActivityRepository implements ActivityRepositoryInterface
{
    public function __construct(
        private readonly ActivityModel $model,
    ) {}

    public function findById(int $id): ?Activity
    {
        $r = $this->model->newQueryWithoutScopes()->find($id);
        return $r ? $this->toEntity($r) : null;
    }

    public function findByContact(int $contactId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('contact_id', $contactId)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function findByOpportunity(int $opportunityId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('opportunity_id', $opportunityId)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function findByLead(int $leadId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('lead_id', $leadId)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function save(array $data): Activity
    {
        $r = $this->model->newInstance($data);
        $r->save();
        return $this->toEntity($r);
    }

    public function update(int $id, array $data): Activity
    {
        $r = $this->model->newQueryWithoutScopes()->findOrFail($id);
        $r->update($data);
        return $this->toEntity($r->fresh());
    }

    public function delete(int $id): void
    {
        $this->model->newQueryWithoutScopes()->findOrFail($id)->delete();
    }

    private function toEntity(ActivityModel $m): Activity
    {
        return new Activity(
            id: $m->id,
            tenantId: $m->tenant_id,
            contactId: $m->contact_id,
            opportunityId: $m->opportunity_id,
            leadId: $m->lead_id,
            type: $m->type,
            subject: $m->subject,
            description: $m->description,
            status: $m->status,
            scheduledAt: $m->scheduled_at ? new \DateTimeImmutable($m->scheduled_at) : null,
            completedAt: $m->completed_at ? new \DateTimeImmutable($m->completed_at) : null,
            assignedTo: $m->assigned_to,
            createdAt: new \DateTimeImmutable($m->created_at),
            updatedAt: new \DateTimeImmutable($m->updated_at),
        );
    }
}
