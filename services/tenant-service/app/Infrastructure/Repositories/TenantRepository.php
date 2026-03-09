<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Support\Repository\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    protected string $model = Tenant::class;

    public function findById(string $id): ?Tenant
    {
        /** @var Tenant|null */
        return $this->newQuery()->find($id);
    }

    public function findByDomain(string $domain): ?Tenant
    {
        /** @var Tenant|null */
        return $this->newQuery()->where('domain', $domain)->first();
    }

    public function findBySlug(string $slug): ?Tenant
    {
        /** @var Tenant|null */
        return $this->newQuery()->where('slug', $slug)->first();
    }

    public function findAll(array $filters = []): Collection|LengthAwarePaginator
    {
        return parent::findAll($filters);
    }

    public function create(array $data): Tenant
    {
        /** @var Tenant */
        return parent::create($data);
    }

    public function update(string $id, array $data): ?Tenant
    {
        /** @var Tenant|null */
        return parent::update($id, $data);
    }

    public function delete(string $id): bool
    {
        return parent::delete($id);
    }

    public function findActive(): Collection
    {
        /** @var Collection<int, Tenant> */
        return $this->newQuery()
            ->where('status', Tenant::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
    }

    public function updateConfig(string $id, array $config): ?Tenant
    {
        $tenant = $this->findById($id);

        if ($tenant === null) {
            return null;
        }

        $existing = $tenant->config ?? [];
        $merged   = array_merge($existing, $config);

        $tenant->config = $merged;
        $tenant->save();

        return $tenant->fresh();
    }

    public function updateDatabaseConfig(string $id, array $databaseConfig): ?Tenant
    {
        $tenant = $this->findById($id);

        if ($tenant === null) {
            return null;
        }

        $tenant->database_config = $databaseConfig;
        $tenant->save();

        return $tenant->fresh();
    }

    public function updateMailConfig(string $id, array $mailConfig): ?Tenant
    {
        $tenant = $this->findById($id);

        if ($tenant === null) {
            return null;
        }

        $tenant->mail_config = $mailConfig;
        $tenant->save();

        return $tenant->fresh();
    }

    public function updateCacheConfig(string $id, array $cacheConfig): ?Tenant
    {
        $tenant = $this->findById($id);

        if ($tenant === null) {
            return null;
        }

        $tenant->cache_config = $cacheConfig;
        $tenant->save();

        return $tenant->fresh();
    }

    public function updateBrokerConfig(string $id, array $brokerConfig): ?Tenant
    {
        $tenant = $this->findById($id);

        if ($tenant === null) {
            return null;
        }

        $tenant->broker_config = $brokerConfig;
        $tenant->save();

        return $tenant->fresh();
    }
}
