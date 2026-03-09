<?php

declare(strict_types=1);

namespace App\Infrastructure\RuntimeConfig;

use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Applies tenant-specific runtime configuration (database, mail, cache, broker,
 * timezone) without requiring a process restart.
 *
 * This class mutates the live Laravel config repository and purges/reconnects
 * relevant driver instances so the changes take effect immediately within the
 * current request/job lifecycle.
 */
final class RuntimeConfigManager
{
    /** Tracks which tenant config is currently applied (per request). */
    private ?string $activeTenantId = null;

    /** Snapshot of defaults so we can restore them. */
    private array $defaults = [];

    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Load the tenant and apply all its runtime configs.
     */
    public function applyTenantConfig(string $tenantId): void
    {
        if ($this->activeTenantId === $tenantId) {
            return; // Already applied for this request
        }

        $tenant = $this->tenantRepository->findById($tenantId);

        if ($tenant === null) {
            throw new RuntimeException("Cannot apply runtime config: tenant '{$tenantId}' not found.");
        }

        $this->captureDefaults();

        $this->applyDatabaseConfig($tenantId, $tenant->database_config ?? []);
        $this->applyMailConfig($tenantId, $tenant->mail_config ?? []);
        $this->applyCacheConfig($tenantId, $tenant->cache_config ?? []);
        $this->applyBrokerConfig($tenantId, $tenant->broker_config ?? []);
        $this->applyTimezone($tenant->getConfig('timezone'));

        $this->activeTenantId = $tenantId;

        Log::debug('Runtime config applied', ['tenant_id' => $tenantId]);
    }

    /**
     * Restore all configuration to its default values.
     */
    public function resetToDefault(): void
    {
        if (empty($this->defaults)) {
            return;
        }

        foreach ($this->defaults as $key => $value) {
            Config::set($key, $value);
        }

        // Purge the tenant-specific DB connection
        if ($this->activeTenantId !== null) {
            try {
                DB::purge("tenant_{$this->activeTenantId}");
            } catch (\Throwable) {
                // Connection may not have been established
            }
        }

        $this->activeTenantId = null;
        $this->defaults       = [];

        Log::debug('Runtime config reset to defaults.');
    }

    /**
     * Return the resolved config array for a tenant (without applying it).
     */
    public function getTenantConfig(string $tenantId): array
    {
        $tenant = $this->tenantRepository->findById($tenantId);

        if ($tenant === null) {
            return [];
        }

        return [
            'database' => $tenant->database_config ?? [],
            'mail'     => $tenant->mail_config     ?? [],
            'cache'    => $tenant->cache_config    ?? [],
            'broker'   => $tenant->broker_config   ?? [],
            'timezone' => $tenant->getConfig('timezone'),
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function captureDefaults(): void
    {
        $this->defaults = [
            'database.default'                  => Config::get('database.default'),
            'mail.default'                      => Config::get('mail.default'),
            'cache.default'                     => Config::get('cache.default'),
            'queue.default'                     => Config::get('queue.default'),
            'app.timezone'                      => Config::get('app.timezone'),
        ];
    }

    private function applyDatabaseConfig(string $tenantId, array $dbConfig): void
    {
        if (empty($dbConfig)) {
            return;
        }

        $connectionName = "tenant_{$tenantId}";

        // Merge tenant overrides on top of the default connection
        $default = Config::get('database.connections.' . Config::get('database.default'), []);

        Config::set("database.connections.{$connectionName}", array_merge($default, [
            'driver'   => $dbConfig['driver']   ?? ($default['driver']   ?? 'mysql'),
            'host'     => $dbConfig['host']      ?? ($default['host']     ?? '127.0.0.1'),
            'port'     => $dbConfig['port']      ?? ($default['port']     ?? 3306),
            'database' => $dbConfig['database']  ?? ($default['database'] ?? ''),
            'username' => $dbConfig['username']  ?? ($default['username'] ?? ''),
            'password' => $dbConfig['password']  ?? ($default['password'] ?? ''),
            'charset'  => $dbConfig['charset']   ?? 'utf8mb4',
            'prefix'   => $dbConfig['prefix']    ?? '',
        ]));

        // Purge any stale connection and force a fresh reconnect on next query
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        Config::set('database.default', $connectionName);

        Log::debug('Database config applied', ['connection' => $connectionName]);
    }

    private function applyMailConfig(string $tenantId, array $mailConfig): void
    {
        if (empty($mailConfig)) {
            return;
        }

        $mailerName = "tenant_{$tenantId}_smtp";

        Config::set("mail.mailers.{$mailerName}", [
            'transport'  => $mailConfig['transport']  ?? 'smtp',
            'host'       => $mailConfig['host']       ?? Config::get('mail.mailers.smtp.host'),
            'port'       => $mailConfig['port']       ?? Config::get('mail.mailers.smtp.port', 587),
            'encryption' => $mailConfig['encryption'] ?? Config::get('mail.mailers.smtp.encryption', 'tls'),
            'username'   => $mailConfig['username']   ?? Config::get('mail.mailers.smtp.username'),
            'password'   => $mailConfig['password']   ?? Config::get('mail.mailers.smtp.password'),
            'timeout'    => $mailConfig['timeout']    ?? null,
        ]);

        if (! empty($mailConfig['from_address'])) {
            Config::set('mail.from.address', $mailConfig['from_address']);
            Config::set('mail.from.name', $mailConfig['from_name'] ?? Config::get('mail.from.name'));
        }

        // Purge cached mailer instance
        try {
            Mail::purge($mailerName);
        } catch (\Throwable) {
            // Not yet instantiated – safe to ignore
        }

        Config::set('mail.default', $mailerName);

        Log::debug('Mail config applied', ['mailer' => $mailerName]);
    }

    private function applyCacheConfig(string $tenantId, array $cacheConfig): void
    {
        if (empty($cacheConfig)) {
            return;
        }

        $storeName = "tenant_{$tenantId}";
        $driver    = $cacheConfig['driver'] ?? 'redis';

        $storeConfig = match ($driver) {
            'redis' => [
                'driver'     => 'redis',
                'connection' => $cacheConfig['connection'] ?? 'default',
                'lock_connection' => $cacheConfig['lock_connection'] ?? 'default',
            ],
            'memcached' => [
                'driver'  => 'memcached',
                'servers' => $cacheConfig['servers'] ?? [['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100]],
            ],
            default => [
                'driver' => $driver,
            ],
        };

        // Always namespace by tenant to ensure isolation
        $storeConfig['prefix'] = $cacheConfig['prefix'] ?? "tenant_{$tenantId}_";

        Config::set("cache.stores.{$storeName}", $storeConfig);
        Config::set('cache.default', $storeName);

        Log::debug('Cache config applied', ['store' => $storeName]);
    }

    private function applyBrokerConfig(string $tenantId, array $brokerConfig): void
    {
        if (empty($brokerConfig)) {
            return;
        }

        $connectionName = "tenant_{$tenantId}";
        $driver         = $brokerConfig['driver'] ?? Config::get('queue.default', 'sync');

        Config::set("queue.connections.{$connectionName}", array_merge(
            Config::get("queue.connections.{$driver}", []),
            [
                'driver' => $driver,
                'host'   => $brokerConfig['host']     ?? null,
                'port'   => $brokerConfig['port']     ?? null,
                'queue'  => $brokerConfig['queue']    ?? "tenant_{$tenantId}",
                'user'   => $brokerConfig['user']     ?? null,
                'password' => $brokerConfig['password'] ?? null,
                'vhost'  => $brokerConfig['vhost']    ?? '/',
            ]
        ));

        Config::set('queue.default', $connectionName);

        Log::debug('Broker config applied', ['connection' => $connectionName]);
    }

    private function applyTimezone(?string $timezone): void
    {
        if ($timezone === null) {
            return;
        }

        // Validate the timezone before applying
        if (! in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            Log::warning('Invalid tenant timezone ignored', ['timezone' => $timezone]);

            return;
        }

        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);
    }
}
