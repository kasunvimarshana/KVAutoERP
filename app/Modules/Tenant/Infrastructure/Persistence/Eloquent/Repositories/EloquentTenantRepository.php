<?php

namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Core\Infrastructure\Persistence\Repositories\EloquentRepository;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\ValueObjects\DatabaseConfig;
use Modules\Tenant\Domain\ValueObjects\MailConfig;
use Modules\Tenant\Domain\ValueObjects\CacheConfig;
use Modules\Tenant\Domain\ValueObjects\QueueConfig;
use Modules\Tenant\Domain\ValueObjects\FeatureFlags;
use Modules\Tenant\Domain\ValueObjects\ApiKeys;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Models\TenantModel;

class EloquentTenantRepository extends EloquentRepository implements TenantRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new TenantModel());
    }

    public function findByDomain(string $domain): ?Tenant
    {
        $model = $this->model->where('domain', $domain)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function get(array $filters = []): \Illuminate\Support\Collection
    {
        $query = $this->model->newQuery();
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (isset($filters['domain'])) {
            $query->where('domain', $filters['domain']);
        }
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }
        $models = $query->get();
        return $models->map(fn($m) => $this->toDomainEntity($m));
    }

    public function paginate(array $filters, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (isset($filters['domain'])) {
            $query->where('domain', $filters['domain']);
        }
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $paginator->getCollection()->transform(fn($m) => $this->toDomainEntity($m));
        return $paginator;
    }

    public function save(Tenant $tenant): Tenant
    {
        $data = [
            'name'            => $tenant->getName(),
            'domain'          => $tenant->getDomain(),
            'logo_path'       => $tenant->getLogoPath(),
            'database_config' => $tenant->getDatabaseConfig()->toArray(),
            'mail_config'     => $tenant->getMailConfig()?->toArray(),
            'cache_config'    => $tenant->getCacheConfig()?->toArray(),
            'queue_config'    => $tenant->getQueueConfig()?->toArray(),
            'feature_flags'   => $tenant->getFeatureFlags()->toArray(),
            'api_keys'        => $tenant->getApiKeys()->toArray(),
            'active'          => $tenant->isActive(),
        ];

        if ($tenant->getId()) {
            $model = $this->update($tenant->getId(), $data);
        } else {
            $model = $this->create($data);
        }
        return $this->toDomainEntity($model);
    }

    public function delete(int $id): bool
    {
        return $this->destroy($id);
    }

    private function toDomainEntity(TenantModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            domain: $model->domain,
            logoPath: $model->logo_path,
            databaseConfig: new DatabaseConfig($model->database_config),
            mailConfig: $model->mail_config ? new MailConfig($model->mail_config) : null,
            cacheConfig: $model->cache_config ? new CacheConfig($model->cache_config) : null,
            queueConfig: $model->queue_config ? new QueueConfig($model->queue_config) : null,
            featureFlags: new FeatureFlags($model->feature_flags ?? []),
            apiKeys: new ApiKeys($model->api_keys ?? []),
            active: $model->active,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}
