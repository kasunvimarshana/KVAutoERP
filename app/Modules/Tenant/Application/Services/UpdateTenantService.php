<?php

declare(strict_types=1);

namespace Modules\Tenant\Application\Services;

use Modules\Core\Domain\ValueObjects\ApiKeys;
use Modules\Core\Domain\ValueObjects\CacheConfig;
use Modules\Core\Domain\ValueObjects\DatabaseConfig;
use Modules\Core\Domain\ValueObjects\FeatureFlags;
use Modules\Core\Domain\ValueObjects\MailConfig;
use Modules\Core\Domain\ValueObjects\QueueConfig;
use Modules\Core\Application\Services\BaseService;
use Modules\Tenant\Application\Contracts\UpdateTenantServiceInterface;
use Modules\Tenant\Domain\Entities\Tenant;
use Modules\Tenant\Domain\Events\TenantUpdated;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class UpdateTenantService extends BaseService implements UpdateTenantServiceInterface
{
    private TenantRepositoryInterface $tenantRepository;

    public function __construct(TenantRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantRepository = $repository;
    }

    protected function handle(array $data): Tenant
    {
        $tenantId = (int) $data['id'];

        $tenant = $this->tenantRepository->find($tenantId);
        if (! $tenant) {
            throw new TenantNotFoundException($tenantId);
        }

        $name = $data['name'] ?? $tenant->getName();
        $slug = $data['slug'] ?? $tenant->getSlug();
        $domain = array_key_exists('domain', $data) ? $data['domain'] : $tenant->getDomain();
        $logoPath = array_key_exists('logo_path', $data) ? $data['logo_path'] : $tenant->getLogoPath();

        $databaseConfig = DatabaseConfig::fromArray($data['database_config'] ?? $tenant->getDatabaseConfig()->toArray());
        $mailConfig = ! empty($data['mail_config'])
            ? MailConfig::fromArray($data['mail_config'])
            : $tenant->getMailConfig();
        $cacheConfig = ! empty($data['cache_config'])
            ? CacheConfig::fromArray($data['cache_config'])
            : $tenant->getCacheConfig();
        $queueConfig = ! empty($data['queue_config'])
            ? QueueConfig::fromArray($data['queue_config'])
            : $tenant->getQueueConfig();
        $featureFlags = new FeatureFlags($data['feature_flags'] ?? $tenant->getFeatureFlags()->toArray());
        $apiKeys = new ApiKeys($data['api_keys'] ?? $tenant->getApiKeys()->toArray());
        $active = array_key_exists('active', $data) ? (bool) $data['active'] : $tenant->isActive();

        $tenant->update(
            name: $name,
            slug: $slug,
            domain: $domain,
            logoPath: $logoPath,
            databaseConfig: $databaseConfig,
            mailConfig: $mailConfig,
            cacheConfig: $cacheConfig,
            queueConfig: $queueConfig,
            featureFlags: $featureFlags,
            apiKeys: $apiKeys,
            active: $active
        );

        $saved = $this->tenantRepository->save($tenant);
        $this->addEvent(new TenantUpdated($saved));

        return $saved;
    }
}
