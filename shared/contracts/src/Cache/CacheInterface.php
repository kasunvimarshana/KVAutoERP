<?php

declare(strict_types=1);

namespace Saas\Contracts\Cache;

/**
 * Tenant-aware cache contract.
 *
 * Implementations MUST automatically namespace cache keys by the active
 * tenant so that data from one tenant can never bleed into another tenant's
 * cache space.  The namespacing strategy (prefix, hash, etc.) is left to the
 * implementation.
 */
interface CacheInterface
{
    /**
     * Retrieves a value from the cache.
     *
     * @param string $key     Cache key (un-namespaced; implementation adds tenant prefix).
     * @param mixed  $default Value returned when the key does not exist or has expired.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Stores a value in the cache.
     *
     * @param string   $key   Cache key.
     * @param mixed    $value Value to store; MUST be serialisable.
     * @param int|null $ttl   Time-to-live in seconds.  `null` stores indefinitely
     *                        (until evicted by the cache store).
     *
     * @return bool `true` on success.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Removes a single key from the cache.
     *
     * @param string $key Cache key to delete.
     *
     * @return bool `true` when the key was removed (or was already absent).
     */
    public function delete(string $key): bool;

    /**
     * Removes all cache entries that share the given prefix within the current
     * tenant namespace.  When `$prefix` is empty the entire tenant namespace is
     * flushed.
     *
     * @param string $prefix Optional key prefix to scope the flush operation.
     *
     * @return bool `true` on success.
     */
    public function flush(string $prefix = ''): bool;

    /**
     * Retrieves a cached value, or executes `$callback` to compute and store it.
     *
     * @param string   $key      Cache key.
     * @param int      $ttl      Time-to-live in seconds for the stored value.
     * @param callable $callback Zero-argument callable whose return value is cached on a miss.
     *
     * @return mixed The cached or freshly computed value.
     */
    public function remember(string $key, int $ttl, callable $callback): mixed;

    /**
     * Scopes subsequent cache operations to the given set of tags.
     *
     * Allows bulk invalidation of logically related keys via a single tag flush.
     * Implementations that do not support tagging MUST throw
     * `\BadMethodCallException`.
     *
     * @param string[] $tags One or more tag names.
     *
     * @return static A new instance scoped to the provided tags.
     */
    public function tags(array $tags): static;
}
