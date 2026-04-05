<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\CRM\Domain\Entities\Contact;
use Modules\CRM\Domain\RepositoryInterfaces\ContactRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ContactModel;

class EloquentContactRepository implements ContactRepositoryInterface
{
    public function __construct(
        private readonly ContactModel $model,
    ) {}

    public function findById(int $id): ?Contact
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByEmail(int $tenantId, string $email): ?Contact
    {
        $record = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('email', $email)
            ->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByType(int $tenantId, string $type): array
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->get()
            ->map(fn (ContactModel $m) => $this->toEntity($m))
            ->all();
    }

    public function create(array $data): Contact
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?Contact
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

    public function search(int $tenantId, string $query): array
    {
        $like = '%' . $query . '%';

        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($like) {
                $q->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('company', 'like', $like);
            })
            ->get()
            ->map(fn (ContactModel $m) => $this->toEntity($m))
            ->all();
    }

    private function toEntity(ContactModel $model): Contact
    {
        return new Contact(
            id: $model->id,
            tenantId: $model->tenant_id,
            type: $model->type,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            phone: $model->phone,
            mobile: $model->mobile,
            company: $model->company,
            jobTitle: $model->job_title,
            address: $model->address ?? [],
            tags: $model->tags ?? [],
            status: $model->status,
            assignedTo: $model->assigned_to,
            notes: $model->notes,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
        );
    }
}
