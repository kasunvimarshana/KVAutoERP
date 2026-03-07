<?php

namespace App\Application\Services;

use App\Domain\Tenant\Entities\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

class TenantConfigManager
{
    private const CACHE_PREFIX = 'tenant_config:';
    private const CACHE_TTL    = 3600;

    public function __construct(private LoggerInterface $logger) {}

    /**
     * Load and apply all tenant-specific configurations at runtime.
     */
    public function loadTenantConfig(string $tenantId): void
    {
        $config = $this->getTenantConfig($tenantId);

        if (!empty($config['database'])) {
            $this->applyDatabaseConfig($tenantId, $config['database']);
        }
        if (!empty($config['cache'])) {
            $this->applyCacheConfig($config['cache']);
        }
        if (!empty($config['mail'])) {
            $this->applyMailConfig($config['mail']);
        }
        if (!empty($config['broker'])) {
            $this->applyBrokerConfig($config['broker']);
        }

        $this->logger->info('Tenant config loaded', ['tenant_id' => $tenantId]);
    }

    /**
     * Retrieve the full tenant config array (cached).
     */
    public function getTenantConfig(string $tenantId): array
    {
        $cacheKey = self::CACHE_PREFIX . $tenantId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);

            return [
                'tenant'   => $tenant->toArray(),
                'database' => $tenant->getDbConnection(),
                'cache'    => $tenant->getCacheConfig(),
                'mail'     => $tenant->getMailConfig(),
                'broker'   => $tenant->getBrokerConfig(),
                'settings' => $tenant->settings ?? [],
            ];
        });
    }

    /**
     * Dynamically register and purge a tenant-specific database connection.
     */
    public function applyDatabaseConfig(string $tenantId, array $dbConfig): void
    {
        if (empty($dbConfig)) {
            return;
        }

        $connectionName = 'tenant_' . $tenantId;

        Config::set("database.connections.{$connectionName}", array_merge([
            'driver'    => 'mysql',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict'    => true,
            'engine'    => null,
        ], $dbConfig));

        // Purge any existing connection so the new config takes effect.
        DB::purge($connectionName);

        $this->logger->debug('Database config applied', [
            'tenant_id'  => $tenantId,
            'connection' => $connectionName,
        ]);
    }

    /**
     * Override the default cache driver/connection for this request lifecycle.
     */
    public function applyCacheConfig(array $cacheConfig): void
    {
        if (empty($cacheConfig)) {
            return;
        }

        Config::set('cache.default', $cacheConfig['driver'] ?? 'redis');

        if (isset($cacheConfig['connection'])) {
            Config::set('cache.stores.redis.connection', $cacheConfig['connection']);
        }

        if (isset($cacheConfig['prefix'])) {
            Config::set('cache.prefix', $cacheConfig['prefix']);
        }
    }

    /**
     * Override mail configuration for the current request.
     */
    public function applyMailConfig(array $mailConfig): void
    {
        if (empty($mailConfig)) {
            return;
        }

        $mailer = $mailConfig['mailer'] ?? 'smtp';
        Config::set('mail.default', $mailer);
        Config::set('mail.mailers.smtp', array_merge(
            Config::get('mail.mailers.smtp', []),
            $mailConfig
        ));

        if (isset($mailConfig['from'])) {
            Config::set('mail.from', $mailConfig['from']);
        }
    }

    /**
     * Override messaging broker configuration for this tenant.
     */
    public function applyBrokerConfig(array $brokerConfig): void
    {
        if (empty($brokerConfig)) {
            return;
        }

        Config::set('messaging.default', $brokerConfig['driver'] ?? 'rabbitmq');

        if (isset($brokerConfig['rabbitmq'])) {
            Config::set('messaging.rabbitmq', array_merge(
                Config::get('messaging.rabbitmq', []),
                $brokerConfig['rabbitmq']
            ));
        }
    }

    /**
     * Remove the cached config and purge the tenant DB connection.
     */
    public function clearTenantConfig(string $tenantId): void
    {
        Cache::forget(self::CACHE_PREFIX . $tenantId);
        DB::purge('tenant_' . $tenantId);

        $this->logger->info('Tenant config cleared', ['tenant_id' => $tenantId]);
    }

    /**
     * Clear and immediately re-load the tenant config.
     */
    public function refreshTenantConfig(string $tenantId): void
    {
        $this->clearTenantConfig($tenantId);
        $this->loadTenantConfig($tenantId);

        $this->logger->info('Tenant config refreshed', ['tenant_id' => $tenantId]);
    }

    /**
     * Return the resolved connection name for a tenant, registering it if needed.
     */
    public function getTenantDatabaseConnection(string $tenantId): string
    {
        $config = $this->getTenantConfig($tenantId);

        if (!empty($config['database'])) {
            $this->applyDatabaseConfig($tenantId, $config['database']);
            return 'tenant_' . $tenantId;
        }

        return config('database.default');
    }
}
