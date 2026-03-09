<?php

declare(strict_types=1);

namespace App\Shared\Tenant;

use App\Shared\Contracts\TenantInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Tenant Manager.
 *
 * Singleton service that maintains the active tenant context for every
 * request in KV_SAAS.  It resolves tenant settings from the database (or
 * cache), reconfigures Laravel's database connection, cache prefix, queue
 * prefix, and mail settings per tenant.
 *
 * Tenant identification is configurable via:
 *  - HTTP header (X-Tenant-ID)
 *  - Sub-domain extraction
 *  - JWT token claim
 *
 * Register in a service provider:
 *   $this->app->singleton(TenantInterface::class, TenantManager::class);
 */
final class TenantManager implements TenantInterface
{
    /** Currently active tenant identifier. */
    private ?string $currentTenantId = null;

    /** Cached raw settings for the current tenant. */
    private array $tenantSettings = [];

    /** Runtime config overrides keyed by config path. */
    private array $runtimeConfig = [];

    /** Name of the tenants table in the system (global) database. */
    private string $tenantsTable = 'tenants';

    /** System (non-tenant) DB connection name. */
    private string $systemConnection = 'mysql';

    /** Cache TTL for tenant settings (seconds). */
    private int $cacheTtl;

    /** DB name prefix applied to every tenant database. */
    private string $dbPrefix;

    public function __construct()
    {
        $this->cacheTtl = (int) config('tenant.cache_ttl', env('TENANT_CACHE_TTL', 300));
        $this->dbPrefix = (string) config('tenant.db_prefix', env('TENANT_DB_PREFIX', 'tenant_'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TenantInterface
    // ─────────────────────────────────────────────────────────────────────────

    /** {@inheritDoc} */
    public function getTenantId(): string
    {
        if ($this->currentTenantId === null) {
            throw new \RuntimeException('No tenant is currently active. Call switchTenant() first.');
        }

        return $this->currentTenantId;
    }

    /** {@inheritDoc} */
    public function getConnectionName(): string
    {
        return 'tenant_' . ($this->currentTenantId ?? 'default');
    }

    /** {@inheritDoc} */
    public function getDatabaseName(): string
    {
        $override = $this->tenantSettings['database'] ?? null;

        return $override ?? $this->dbPrefix . ($this->currentTenantId ?? '');
    }

    /** {@inheritDoc} */
    public function getConfiguration(string $key, mixed $default = null): mixed
    {
        // 1. Runtime overrides set during switchTenant()
        if (array_key_exists($key, $this->runtimeConfig)) {
            return $this->runtimeConfig[$key];
        }

        // 2. Tenant-specific settings stored in DB/cache
        if (array_key_exists($key, $this->tenantSettings)) {
            return $this->tenantSettings[$key];
        }

        // 3. Application config
        return config($key, $default);
    }

    /**
     * {@inheritDoc}
     *
     * Steps:
     * 1. Load tenant row from DB (or cache).
     * 2. Set current tenant ID.
     * 3. Dynamically register a Tenant DB connection.
     * 4. Override cache prefix, queue, mail, etc.
     */
    public function switchTenant(string $tenantId): void
    {
        $settings = $this->loadTenantSettings($tenantId);

        $this->currentTenantId = $tenantId;
        $this->tenantSettings  = $settings;
        $this->runtimeConfig   = [];

        $this->configureDatabaseConnection($tenantId, $settings);
        $this->configureCachePrefix($tenantId, $settings);
        $this->configureQueue($tenantId, $settings);
        $this->configureMail($settings);
    }

    /** {@inheritDoc} */
    public function getTenantSettings(): array
    {
        return $this->tenantSettings;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Runtime config
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Set a configuration value that persists only for the current request
     * (not written to any persistent store).
     *
     * @param  string  $key    Dot-notation Laravel config key.
     * @param  mixed   $value
     * @return void
     */
    public function setRuntimeConfig(string $key, mixed $value): void
    {
        Config::set($key, $value);
        $this->runtimeConfig[$key] = $value;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tenant resolution helpers (called by TenantMiddleware)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve a tenant ID from the incoming HTTP request.
     *
     * Resolution order:
     *  1. X-Tenant-ID header
     *  2. Sub-domain (e.g. acme.kv-saas.com → "acme")
     *  3. JWT 'tid' claim (if a valid JWT is present)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function resolveFromRequest(\Illuminate\Http\Request $request): ?string
    {
        // 1. Explicit header
        if ($tenantId = $request->header('X-Tenant-ID')) {
            return $tenantId;
        }

        // 2. Sub-domain
        $host = $request->getHost();
        $appDomain = config('app.domain', parse_url(config('app.url', ''), PHP_URL_HOST) ?? '');
        if ($appDomain && str_ends_with($host, '.' . $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
            if (!empty($subdomain) && $subdomain !== 'www') {
                return $subdomain;
            }
        }

        // 3. JWT 'tid' claim
        $token = $request->bearerToken();
        if ($token) {
            return $this->extractTenantFromJwt($token);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Load (and cache) tenant settings from the tenants table.
     *
     * @param  string  $tenantId
     * @return array<string,mixed>
     *
     * @throws \RuntimeException  If the tenant does not exist.
     */
    private function loadTenantSettings(string $tenantId): array
    {
        $cacheKey = "tenant_settings_{$tenantId}";

        $settings = Cache::remember($cacheKey, $this->cacheTtl, function () use ($tenantId): array {
            $tenant = DB::connection($this->systemConnection)
                ->table($this->tenantsTable)
                ->where('id', $tenantId)
                ->orWhere('slug', $tenantId)
                ->first();

            if ($tenant === null) {
                throw new \RuntimeException("Tenant [{$tenantId}] not found.");
            }

            $raw = (array) $tenant;

            // Decode JSON settings column if present.
            if (isset($raw['settings']) && is_string($raw['settings'])) {
                $decoded = json_decode($raw['settings'], associative: true);
                $raw = array_merge($raw, is_array($decoded) ? $decoded : []);
            }

            return $raw;
        });

        return $settings;
    }

    /**
     * Dynamically register and configure the tenant's DB connection.
     */
    private function configureDatabaseConnection(string $tenantId, array $settings): void
    {
        $connectionName = $this->getConnectionName();
        $dbName         = $this->getDatabaseName();

        // Base the tenant connection on the system connection config.
        $base = Config::get('database.connections.' . $this->systemConnection, []);

        $tenantConnection = array_merge($base, [
            'database' => $settings['db_name'] ?? $dbName,
            'host'     => $settings['db_host'] ?? $base['host'] ?? env('DB_HOST', 'mysql'),
            'port'     => $settings['db_port'] ?? $base['port'] ?? 3306,
            'username' => $settings['db_username'] ?? $base['username'] ?? env('DB_USERNAME'),
            'password' => $settings['db_password'] ?? $base['password'] ?? env('DB_PASSWORD'),
            'prefix'   => $settings['db_table_prefix'] ?? '',
        ]);

        Config::set("database.connections.{$connectionName}", $tenantConnection);

        // Purge any cached resolver so the new config takes effect.
        DB::purge($connectionName);
        DB::reconnect($connectionName);

        // Set as the default connection for the request.
        DB::setDefaultConnection($connectionName);
        Config::set('database.default', $connectionName);
    }

    /**
     * Set a per-tenant cache prefix so tenants never share cache keys.
     */
    private function configureCachePrefix(string $tenantId, array $settings): void
    {
        $prefix = $settings['cache_prefix'] ?? "tenant_{$tenantId}_";
        Config::set('cache.prefix', $prefix);
    }

    /**
     * Set a per-tenant queue name/connection.
     */
    private function configureQueue(string $tenantId, array $settings): void
    {
        if (!empty($settings['queue_connection'])) {
            Config::set('queue.default', $settings['queue_connection']);
        }

        if (!empty($settings['queue_name'])) {
            Config::set('queue.connections.' . config('queue.default') . '.queue', $settings['queue_name']);
        }
    }

    /**
     * Override mail FROM address / name per tenant.
     */
    private function configureMail(array $settings): void
    {
        if (!empty($settings['mail_from_address'])) {
            Config::set('mail.from.address', $settings['mail_from_address']);
        }

        if (!empty($settings['mail_from_name'])) {
            Config::set('mail.from.name', $settings['mail_from_name']);
        }
    }

    /**
     * Decode a JWT payload and extract the 'tid' (tenant ID) claim.
     *
     * Does NOT verify the signature here – the auth middleware is responsible
     * for signature verification; this is only claim extraction.
     *
     * @param  string  $token
     * @return string|null
     */
    private function extractTenantFromJwt(string $token): ?string
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );

            return $payload['tid'] ?? $payload['tenant_id'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }
}
