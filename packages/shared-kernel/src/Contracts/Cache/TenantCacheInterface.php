<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Contracts\Cache;

use KvEnterprise\SharedKernel\ValueObjects\TenantId;

/**
 * Contract for a tenant-scoped cache store.
 *
 * All cache keys are automatically namespaced under the tenant
 * to guarantee strict isolation between tenants sharing the same
 * underlying cache infrastructure (e.g., a shared Redis cluster).
 */
interface TenantCacheInterface
{
    /**
     * Retrieve an item from the tenant-scoped cache.
     *
     * @param  TenantId  $tenantId  The owning tenant.
     * @param  string    $key       Cache key (without tenant prefix).
     * @param  mixed     $default   Value returned when the key is absent.
     * @return mixed                 The cached value or $default.
     */
    public function get(TenantId $tenantId, string $key, mixed $default = null): mixed;

    /**
     * Store an item in the tenant-scoped cache.
     *
     * @param  TenantId   $tenantId  The owning tenant.
     * @param  string     $key       Cache key (without tenant prefix).
     * @param  mixed      $value     The value to cache.
     * @param  int|null   $ttl       Time-to-live in seconds; null means forever.
     * @return bool                   True on success.
     */
    public function put(TenantId $tenantId, string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Store an item only if it does not already exist (atomic set-if-not-exists).
     *
     * @param  TenantId  $tenantId  The owning tenant.
     * @param  string    $key       Cache key (without tenant prefix).
     * @param  mixed     $value     The value to cache.
     * @param  int|null  $ttl       Time-to-live in seconds; null means forever.
     * @return bool                  True if the item was stored, false if the key existed.
     */
    public function add(TenantId $tenantId, string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Remove a single item from the tenant-scoped cache.
     *
     * @param  TenantId  $tenantId  The owning tenant.
     * @param  string    $key       Cache key to invalidate.
     * @return bool                  True if the item existed and was removed.
     */
    public function forget(TenantId $tenantId, string $key): bool;

    /**
     * Flush all cache entries belonging to the given tenant.
     *
     * This operation does NOT affect other tenants' cache data.
     *
     * @param  TenantId  $tenantId  The tenant whose cache should be cleared.
     * @return bool                  True on success.
     */
    public function flush(TenantId $tenantId): bool;

    /**
     * Determine whether a key exists in the tenant-scoped cache.
     *
     * @param  TenantId  $tenantId  The owning tenant.
     * @param  string    $key       Cache key to check.
     * @return bool                  True if the key exists and has not expired.
     */
    public function has(TenantId $tenantId, string $key): bool;

    /**
     * Retrieve an item from the cache or execute the callback to populate it.
     *
     * @param  TenantId   $tenantId  The owning tenant.
     * @param  string     $key       Cache key (without tenant prefix).
     * @param  int|null   $ttl       Time-to-live in seconds; null means forever.
     * @param  callable   $callback  Invoked to compute the value on a cache miss.
     * @return mixed                  The cached or freshly computed value.
     */
    public function remember(TenantId $tenantId, string $key, ?int $ttl, callable $callback): mixed;

    /**
     * Increment a numeric cache value for the given tenant and key.
     *
     * @param  TenantId  $tenantId  The owning tenant.
     * @param  string    $key       Cache key to increment.
     * @param  int       $by        Amount to increment by (default 1).
     * @return int                   The new value after incrementing.
     */
    public function increment(TenantId $tenantId, string $key, int $by = 1): int;

    /**
     * Decrement a numeric cache value for the given tenant and key.
     *
     * @param  TenantId  $tenantId  The owning tenant.
     * @param  string    $key       Cache key to decrement.
     * @param  int       $by        Amount to decrement by (default 1).
     * @return int                   The new value after decrementing.
     */
    public function decrement(TenantId $tenantId, string $key, int $by = 1): int;
}
