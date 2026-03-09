<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Runtime Config Manager.
 *
 * Dynamically reconfigures Laravel's runtime configuration for the active
 * tenant — database connections, cache, mail, and queue settings.
 *
 * This is used both when switching tenant contexts and when a config key is
 * updated via the API during a live request.
 */
final class RuntimeConfigManager
{
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Load all persisted configuration for a tenant and apply it at runtime.
     *
     * @param  string  $tenantId  Tenant UUID.
     */
    public function loadTenantConfig(string $tenantId): void
    {
        // Retrieve all configuration from the database.
        /** @var \App\Infrastructure\Persistence\Models\TenantConfiguration[] $configs */
        $configs = \App\Infrastructure\Persistence\Models\TenantConfiguration::query()
            ->where('tenant_id', $tenantId)
            ->where('environment', config('app.env', 'production'))
            ->get();

        $flat = [];
        foreach ($configs as $config) {
            $entity = \App\Domain\Tenant\Entities\TenantConfiguration::fromArray($config->toArray());
            $flat[$entity->getConfigKey()] = $entity->getValue();
        }

        $this->applyConfig($flat);

        $this->logger->debug('[RuntimeConfigManager] Tenant config loaded', [
            'tenant_id' => $tenantId,
            'keys'      => array_keys($flat),
        ]);
    }

    /**
     * Apply an arbitrary key→value config map to the running Laravel application.
     *
     * Recognised top-level key prefixes:
     *  - `database.*`  → reconfigures database connection
     *  - `mail.*`      → reconfigures mail driver
     *  - `cache.*`     → reconfigures cache store
     *  - `queue.*`     → reconfigures queue connection
     *  - Everything else is set directly via config()
     *
     * @param  array<string, mixed>  $config
     */
    public function applyConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            $prefix = strtok($key, '.');

            match ($prefix) {
                'database' => $this->setDatabaseConfig($key, $value),
                'mail'     => $this->setMailConfig($key, $value),
                'cache'    => $this->setCacheConfig($key, $value),
                'queue'    => $this->setQueueConfig($key, $value),
                default    => Config::set($key, $value),
            };
        }
    }

    /**
     * Reconfigure the tenant's database connection at runtime.
     *
     * @param  array<string, mixed>  $config  Full DB connection config array.
     */
    public function setDatabaseConnection(array $config): void
    {
        $connectionName = $config['name'] ?? 'tenant';
        Config::set('database.connections.' . $connectionName, $config);
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        $this->logger->debug('[RuntimeConfigManager] DB connection updated', [
            'connection' => $connectionName,
        ]);
    }

    /**
     * Apply mail configuration at runtime.
     *
     * @param  array<string, mixed>  $config  Partial or full mail config.
     */
    public function setMailConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            Config::set('mail.' . $key, $value);
        }
    }

    /**
     * Apply cache configuration at runtime.
     *
     * @param  array<string, mixed>  $config  Partial or full cache config.
     */
    public function setCacheConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            Config::set('cache.' . $key, $value);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function setDatabaseConfig(string $key, mixed $value): void
    {
        Config::set($key, $value);
    }

    private function setQueueConfig(string $key, mixed $value): void
    {
        Config::set($key, $value);
    }
}
