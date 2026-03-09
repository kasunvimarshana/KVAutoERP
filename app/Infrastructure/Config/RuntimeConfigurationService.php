<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * RuntimeConfigurationService
 *
 * Allows tenant-specific runtime configuration of:
 *   - Database connections
 *   - Cache drivers
 *   - Mail/email services
 *   - Message broker driver
 *   - Any arbitrary Laravel config key
 *
 * Changes take effect immediately without requiring an application restart.
 * Configurations are stored per-tenant in the database and loaded on each request.
 */
class RuntimeConfigurationService
{
    private const CACHE_PREFIX = 'tenant_config_';
    private const CACHE_TTL    = 3600; // 1 hour

    public function __construct(
        private readonly int|string|null $tenantId = null
    ) {}

    // -------------------------------------------------------------------------
    //  Public API
    // -------------------------------------------------------------------------

    /**
     * Apply a full set of tenant configurations at runtime.
     *
     * @param  array<string,mixed> $configurations  ['config.key' => value, ...]
     */
    public function applyConfigurations(array $configurations): void
    {
        foreach ($configurations as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Set a single runtime configuration value.
     *
     * @param  string $key    Dot-notation Laravel config key, e.g. "mail.default"
     * @param  mixed  $value
     */
    public function set(string $key, mixed $value): void
    {
        Config::set($key, $value);
        Log::debug("[RuntimeConfig] Set [{$key}] for tenant [{$this->tenantId}]");
    }

    /**
     * Retrieve a runtime configuration value.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }

    /**
     * Apply the tenant's database connection at runtime.
     *
     * @param  array<string,mixed> $connectionConfig
     * @param  string              $connectionName    Defaults to "tenant"
     */
    public function applyDatabaseConnection(
        array $connectionConfig,
        string $connectionName = 'tenant'
    ): void {
        Config::set("database.connections.{$connectionName}", $connectionConfig);

        // Purge existing connection so Laravel picks up the new config
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        Log::info("[RuntimeConfig] Database connection [{$connectionName}] updated for tenant [{$this->tenantId}]");
    }

    /**
     * Apply the tenant's email configuration at runtime.
     *
     * @param  array<string,mixed> $mailConfig
     */
    public function applyMailConfiguration(array $mailConfig): void
    {
        foreach ($mailConfig as $key => $value) {
            Config::set("mail.{$key}", $value);
        }

        // Reset the mail manager so it picks up new settings
        app()->forgetInstance('mailer');
        app()->forgetInstance('swift.mailer');

        Log::info("[RuntimeConfig] Mail configuration updated for tenant [{$this->tenantId}]");
    }

    /**
     * Apply the tenant's cache driver configuration at runtime.
     *
     * @param  string              $driver   e.g. "redis", "memcached", "file"
     * @param  array<string,mixed> $options  Driver-specific options
     */
    public function applyCacheDriver(string $driver, array $options = []): void
    {
        Config::set('cache.default', $driver);

        if (! empty($options)) {
            Config::set("cache.stores.{$driver}", array_merge(
                Config::get("cache.stores.{$driver}", []),
                $options
            ));
        }

        // Reset the cache manager
        app()->forgetInstance('cache');

        Log::info("[RuntimeConfig] Cache driver set to [{$driver}] for tenant [{$this->tenantId}]");
    }

    /**
     * Load tenant-specific configurations from the database/cache and apply them.
     *
     * @param  int|string $tenantId
     */
    public function loadTenantConfigurations(int|string $tenantId): void
    {
        $cacheKey = self::CACHE_PREFIX . $tenantId;

        $configurations = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenantId): array {
            return DB::table('tenant_configurations')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->pluck('value', 'key')
                ->map(fn ($v) => json_decode($v, true) ?? $v)
                ->all();
        });

        $this->applyConfigurations($configurations);
    }

    /**
     * Flush the cached configuration for a tenant (e.g. after an admin update).
     *
     * @param  int|string $tenantId
     */
    public function flushTenantCache(int|string $tenantId): void
    {
        Cache::forget(self::CACHE_PREFIX . $tenantId);
    }
}
