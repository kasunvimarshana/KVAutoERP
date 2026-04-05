<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\CRM\Domain\Entities\Activity;
use Modules\CRM\Domain\RepositoryInterfaces\ActivityRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ActivityModel;

class EloquentActivityRepository implements ActivityRepositoryInterface
{
    public function __construct(
        private readonly ActivityModel $model,
    ) {}

    public function findById(int $id): ?Activity
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByRelated(string $relatedType, int $relatedId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->get()
            ->map(fn (ActivityModel $m) => $this->toEntity($m))
            ->all();
    }

    public function findPending(int $tenantId): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->get()
            ->map(fn (ActivityModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Activity
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Activity
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

    private function toEntity(ActivityModel $model): Activity
    {
        return new Activity(
            id: $model->id,
            tenantId: $model->tenant_id,
            relatedType: $model->related_type,
            relatedId: $model->related_id,
            type: $model->type,
            subject: $model->subject,
            description: $model->description,
            scheduledAt: $model->scheduled_at?->toDateTime(),
            completedAt: $model->completed_at?->toDateTime(),
            status: $model->status,
            assignedTo: $model->assigned_to,
            createdAt: $model->created_at,
        );
    }
}
