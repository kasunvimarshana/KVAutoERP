<?php

declare(strict_types=1);

namespace LaravelDDD\Contracts;

/**
 * Contract for registering and discovering DDD bounded contexts.
 */
interface ContextRegistrar
{
    /**
     * Register a bounded context manually.
     *
     * @param  string  $contextName  The name of the context (e.g. "ProductCatalog").
     * @param  string  $contextPath  Absolute filesystem path to the context root.
     * @return void
     */
    public function register(string $contextName, string $contextPath): void;

    /**
     * Retrieve all registered contexts.
     *
     * @return array<string, array{name: string, path: string, namespace: string}>
     */
    public function all(): array;

    /**
     * Retrieve a single context by name.
     *
     * @param  string  $contextName
     * @return array{name: string, path: string, namespace: string}|null
     */
    public function get(string $contextName): ?array;

    /**
     * Determine whether a context is registered.
     *
     * @param  string  $contextName
     * @return bool
     */
    public function has(string $contextName): bool;

    /**
     * Auto-discover contexts under the given base path.
     *
     * A directory is treated as a context when it contains a Domain/ sub-directory.
     *
     * @param  string  $basePath  Absolute path to scan.
     * @return void
     */
    public function discover(string $basePath): void;
}
