<?php

declare(strict_types=1);

namespace Modules\CRM\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\CRM\Domain\Entities\Contact;
use Modules\CRM\Domain\RepositoryInterfaces\ContactRepositoryInterface;
use Modules\CRM\Infrastructure\Persistence\Eloquent\Models\ContactModel;

final class EloquentContactRepository implements ContactRepositoryInterface
{
    public function __construct(
        private readonly ContactModel $model,
    ) {}

    public function findById(int $id): ?Contact
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

    public function findByType(int $tenantId, string $type): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('type', $type)
            ->get()->map(fn ($r) => $this->toEntity($r));
    }

    public function findByEmail(int $tenantId, string $email): ?Contact
    {
        $r = $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->where('email', $email)
            ->first();
        return $r ? $this->toEntity($r) : null;
    }

    public function save(array $data): Contact
    {
        $r = $this->model->newInstance($data);
        $r->save();
        return $this->toEntity($r);
    }

    public function update(int $id, array $data): Contact
    {
        $r = $this->model->newQueryWithoutScopes()->findOrFail($id);
        $r->update($data);
        return $this->toEntity($r->fresh());
    }

    public function delete(int $id): void
    {
        $this->model->newQueryWithoutScopes()->findOrFail($id)->delete();
    }

    private function toEntity(ContactModel $m): Contact
    {
        return new Contact(
            id: $m->id,
            tenantId: $m->tenant_id,
            type: $m->type,
            name: $m->name,
            email: $m->email,
            phone: $m->phone,
            mobile: $m->mobile,
            company: $m->company,
            position: $m->position,
            address: $m->address,
            taxNumber: $m->tax_number,
            currencyCode: $m->currency_code,
            creditLimit: (float) $m->credit_limit,
            paymentTerms: (int) $m->payment_terms,
            notes: $m->notes,
            isActive: (bool) $m->is_active,
            tags: $m->tags,
            customFields: $m->custom_fields,
            createdAt: new \DateTimeImmutable($m->created_at),
            updatedAt: new \DateTimeImmutable($m->updated_at),
        );
    }
}
