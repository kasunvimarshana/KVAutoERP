<?php

declare(strict_types=1);

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function __construct(
        private readonly TenantModel $model,
    ) {}

    public function findById(int $id): ?Tenant
    {
        $model = $this->model->newQuery()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $model = $this->model->newQuery()->where('slug', $slug)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(int $page = 1, int $perPage = 15): array
    {
        $paginator = $this->model->newQuery()->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items() ? array_map([$this, 'toEntity'], $paginator->items()) : [],
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function save(Tenant $tenant): Tenant
    {
        if ($tenant->id === null) {
            $model = $this->model->newQuery()->create($this->toArray($tenant));
        } else {
            $model = $this->model->newQuery()->findOrFail($tenant->id);
            $model->update($this->toArray($tenant));
            $model->refresh();
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        $this->model->newQuery()->findOrFail($id)->delete();
    }

    private function toEntity(TenantModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            domain: $model->domain,
            database: $model->database,
            status: $model->status,
            plan: $model->plan,
            locale: $model->locale,
            timezone: $model->timezone,
            currency: $model->currency,
            settings: $model->settings ?? [],
            trialEndsAt: $model->trial_ends_at,
            suspendedAt: $model->suspended_at,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    private function toArray(Tenant $tenant): array
    {
        return [
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'database' => $tenant->database,
            'status' => $tenant->status,
            'plan' => $tenant->plan,
            'locale' => $tenant->locale,
            'timezone' => $tenant->timezone,
            'currency' => $tenant->currency,
            'settings' => $tenant->settings ? json_encode($tenant->settings) : null,
            'trial_ends_at' => $tenant->trialEndsAt,
            'suspended_at' => $tenant->suspendedAt,
        ];
    }
}
