<?php

namespace Shared\Core\MultiTenancy;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantManager
{
    /**
     * @var string|null
     */
    protected $currentTenantId;

    /**
     * @var array
     */
    protected $tenantConfig = [];

    /**
     * Set the current tenant and update configurations
     *
     * @param string $tenantId
     * @param array $config
     * @return void
     */
    public function setTenant(string $tenantId, array $config): void
    {
        $this->currentTenantId = $tenantId;
        $this->tenantConfig = $config;

        $this->applyDatabaseConfig($config['database'] ?? []);
        $this->applyMailConfig($config['mail'] ?? []);
        $this->applyCacheConfig($config['cache'] ?? []);
        $this->applyQueueConfig($config['queue'] ?? []);
        $this->applyApiKeys($config['api_keys'] ?? []);
        $this->applyFeatureFlags($config['feature_flags'] ?? []);

        Log::info("Tenant set: {$tenantId}");
    }

    /**
     * Get the current tenant ID
     *
     * @return string|null
     */
    public function getTenantId(): ?string
    {
        return $this->currentTenantId;
    }

    /**
     * Apply database configuration dynamically
     *
     * @param array $config
     * @return void
     */
    protected function applyDatabaseConfig(array $config): void
    {
        if (empty($config)) return;

        $connectionName = "tenant_{$this->currentTenantId}";

        Config::set("database.connections.{$connectionName}", array_merge(
            Config::get('database.connections.mysql') ?? [],
            $config
        ));

        DB::purge($connectionName);
        DB::setDefaultConnection($connectionName);
    }

    /**
     * Apply mail configuration dynamically
     *
     * @param array $config
     * @return void
     */
    protected function applyMailConfig(array $config): void
    {
        if (empty($config)) return;

        Config::set('mail.mailers.smtp', array_merge(
            Config::get('mail.mailers.smtp') ?? [],
            $config
        ));
    }

    /**
     * Apply cache configuration dynamically
     *
     * @param array $config
     * @return void
     */
    protected function applyCacheConfig(array $config): void
    {
        if (empty($config)) return;

        Config::set('cache.stores.redis', array_merge(
            Config::get('cache.stores.redis') ?? [],
            $config
        ));
    }

    /**
     * Apply queue configuration dynamically
     *
     * @param array $config
     * @return void
     */
    protected function applyQueueConfig(array $config): void
    {
        if (empty($config)) return;

        Config::set('queue.connections.redis', array_merge(
            Config::get('queue.connections.redis') ?? [],
            $config
        ));
    }

    /**
     * Apply API keys dynamically
     *
     * @param array $config
     * @return void
     */
    protected function applyApiKeys(array $config): void
    {
        if (empty($config)) return;

        foreach ($config as $key => $value) {
            Config::set("services.{$key}.key", $value);
        }
    }

    /**
     * Apply feature flags dynamically
     *
     * @param array $config
     * @return void
     */
    protected function applyFeatureFlags(array $config): void
    {
        if (empty($config)) return;

        Config::set('features.flags', $config);
    }
}
