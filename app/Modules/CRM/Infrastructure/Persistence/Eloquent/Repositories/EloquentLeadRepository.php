<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Lead;
use Modules\CRM\Domain\RepositoryInterfaces\LeadRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\LeadModel;

final class EloquentLeadRepository implements LeadRepositoryInterface
{
    public function __construct(
        private readonly LeadModel $model,
    ) {}

    public function findById(int $id): ?Lead
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

    public function findByStatus(int $tenantId, string $status): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function save(array $data): Lead
    {
        $r = $this->model->newInstance($data);
        $r->save();
        return $this->toEntity($r);
    }

    public function update(int $id, array $data): Lead
    {
        $r = $this->model->newQueryWithoutScopes()->findOrFail($id);
        $r->update($data);
        return $this->toEntity($r->fresh());
    }

    public function delete(int $id): void
    {
        $this->model->newQueryWithoutScopes()->findOrFail($id)->delete();
    }

    private function toEntity(LeadModel $m): Lead
    {
        return new Lead(
            id: $m->id,
            tenantId: $m->tenant_id,
            contactId: $m->contact_id,
            name: $m->name,
            email: $m->email,
            phone: $m->phone,
            company: $m->company,
            source: $m->source,
            status: $m->status,
            score: (int) $m->score,
            assignedTo: $m->assigned_to,
            notes: $m->notes,
            expectedValue: $m->expected_value !== null ? (float) $m->expected_value : null,
            createdAt: new \DateTimeImmutable($m->created_at),
            updatedAt: new \DateTimeImmutable($m->updated_at),
        );
    }
}
