<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Tenant-aware cache that prefixes every key with the tenant ID,
 * preventing data bleed between tenants on a shared cache store.
 */
class TenantAwareCache
{
    private string $tenantId = 'global';

    public function __construct(
        private readonly Repository $cache,
    ) {}

    /**
     * Set the active tenant ID for all subsequent operations.
     */
    public function setTenant(string $tenantId): static
    {
        $this->tenantId = $tenantId;

        return $this;
    }

    /**
     * Get the current tenant prefix.
     */
    public function prefix(): string
    {
        return "tenant:{$this->tenantId}:";
    }

    /**
     * Build a tenant-prefixed cache key.
     */
    public function key(string $key): string
    {
        return $this->prefix() . $key;
    }

    // -------------------------------------------------------------------------
    // Delegated cache operations
    // -------------------------------------------------------------------------

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($this->key($key), $default);
    }

    public function put(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        return $this->cache->put($this->key($key), $value, $ttl);
    }

    public function remember(string $key, \DateInterval|int|null $ttl, \Closure $callback): mixed
    {
        return $this->cache->remember($this->key($key), $ttl, $callback);
    }

    public function rememberForever(string $key, \Closure $callback): mixed
    {
        return $this->cache->rememberForever($this->key($key), $callback);
    }

    public function forget(string $key): bool
    {
        return $this->cache->forget($this->key($key));
    }

    public function has(string $key): bool
    {
        return $this->cache->has($this->key($key));
    }

    /**
     * Flush all cache entries for the current tenant.
     * Requires a cache driver that supports tagging (Redis, Memcached).
     */
    public function flush(): bool
    {
        try {
            return $this->cache->tags(["tenant:{$this->tenantId}"])->flush();
        } catch (\BadMethodCallException) {
            // Driver does not support tags – noop (e.g. file, array drivers in tests)
            return false;
        }
    }

    /**
     * Store a value with tenant tags for tag-based flushing.
     */
    public function tagged(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        try {
            return $this->cache
                ->tags(["tenant:{$this->tenantId}"])
                ->put($this->key($key), $value, $ttl);
        } catch (\BadMethodCallException) {
            return $this->put($key, $value, $ttl);
        }
    }
}
