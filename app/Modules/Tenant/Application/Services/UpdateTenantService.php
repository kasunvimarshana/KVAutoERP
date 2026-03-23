<?php

namespace Modules\Tenant\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Application\DTOs\TenantData;
use Modules\Tenant\Domain\Events\TenantUpdated;

class UpdateTenantService extends BaseService
{
    public function __construct(TenantRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function handle(array $data): Tenant
    {
        $id = $data['id'];
        $dto = TenantData::fromArray($data);

        $tenant = $this->repository->find($id);
        if (!$tenant) {
            throw new \RuntimeException('Tenant not found');
        }

        // Update basic fields
        $tenant = new Tenant(
            id: $tenant->getId(),
            name: $dto->name,
            domain: $dto->domain,
            databaseConfig: new DatabaseConfig($dto->database_config),
            mailConfig: $dto->mail_config ? new MailConfig($dto->mail_config) : null,
            cacheConfig: $dto->cache_config ? new CacheConfig($dto->cache_config) : null,
            queueConfig: $dto->queue_config ? new QueueConfig($dto->queue_config) : null,
            featureFlags: new FeatureFlags($dto->feature_flags),
            apiKeys: new ApiKeys($dto->api_keys),
            active: $dto->active,
            createdAt: $tenant->getCreatedAt(),
            updatedAt: new \DateTimeImmutable()
        );

        $saved = $this->repository->save($tenant);
        $this->addEvent(new TenantUpdated($saved));
        return $saved;
    }
}
