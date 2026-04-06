<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Exceptions\NotFoundException;
use Modules\Tenant\Application\Contracts\TenantServiceInterface;
use Modules\Tenant\Domain\Events\TenantCreated;
use Modules\Tenant\Domain\Events\TenantUpdated;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class TenantService implements TenantServiceInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $repository,
    ) {}

    public function create(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $tenant = $this->repository->create($data);

            if (app()->bound('events')) {
                $entity = $this->mapToEntity($tenant);
                event(new TenantCreated($entity, $tenant->id));
            }

            return $tenant;
        });
    }

    public function update(int $id, array $data): mixed
    {
        return DB::transaction(function () use ($id, $data) {
            $tenant = $this->repository->findById($id);
            if (! $tenant) {
                throw new NotFoundException('Tenant', $id);
            }

            $updated = $this->repository->update($id, $data);

            if (app()->bound('events') && $updated) {
                $entity = $this->mapToEntity($updated);
                event(new TenantUpdated($entity, $updated->id));
            }

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $tenant = $this->repository->findById($id);
            if (! $tenant) {
                throw new NotFoundException('Tenant', $id);
            }

            return $this->repository->delete($id);
        });
    }

    public function find(int $id): mixed
    {
        return $this->repository->findById($id);
    }

    public function findAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->findAll($perPage, $page);
    }

    public function findBySlug(string $slug): mixed
    {
        return $this->repository->findBySlug($slug);
    }

    public function findByDomain(string $domain): mixed
    {
        return $this->repository->findByDomain($domain);
    }

    public function activate(int $id): mixed
    {
        return $this->update($id, ['is_active' => true]);
    }

    public function deactivate(int $id): mixed
    {
        return $this->update($id, ['is_active' => false]);
    }

    private function mapToEntity(mixed $model): \Modules\Tenant\Domain\Entities\Tenant
    {
        return new \Modules\Tenant\Domain\Entities\Tenant(
            id: (int) $model->id,
            name: $model->name,
            slug: $model->slug,
            domain: $model->domain,
            email: $model->email,
            phone: $model->phone,
            address: $model->address,
            isActive: (bool) $model->is_active,
            planId: $model->plan_id ? (int) $model->plan_id : null,
            settings: $model->settings,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()) : null,
            updatedAt: $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()) : null,
        );
    }
}
