<?php

declare(strict_types=1);

namespace LaravelDDD\Resolvers;

use Illuminate\Filesystem\Filesystem;
use LaravelDDD\Contracts\ContextRegistrar;

/**
 * Discovers and registers DDD bounded contexts from the filesystem.
 */
class ContextResolver implements ContextRegistrar
{
    /** @var array<string, array{name: string, path: string, namespace: string}> */
    protected array $contexts = [];

    /**
     * @param  Filesystem  $files
     * @param  string  $namespaceRoot  Root namespace (e.g. "App").
     */
    public function __construct(
        protected Filesystem $files,
        protected string $namespaceRoot = 'App',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function register(string $contextName, string $contextPath): void
    {
        $this->contexts[$contextName] = [
            'name'      => $contextName,
            'path'      => rtrim($contextPath, '/\\'),
            'namespace' => $this->resolveNamespace($contextName),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->contexts;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $contextName): ?array
    {
        return $this->contexts[$contextName] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $contextName): bool
    {
        return isset($this->contexts[$contextName]);
    }

    /**
     * {@inheritdoc}
     */
    public function discover(string $basePath): void
    {
        if (! $this->files->isDirectory($basePath)) {
            return;
        }

        $directories = $this->files->directories($basePath);

        foreach ($directories as $directory) {
            $contextName = basename($directory);

            // A DDD context must have a Domain sub-directory
            if ($this->files->isDirectory($directory.DIRECTORY_SEPARATOR.'Domain')) {
                $this->register($contextName, $directory);
            }
        }
    }

    /**
     * Resolve the fully-qualified namespace for a context name.
     *
     * @param  string  $contextName
     * @return string
     */
    protected function resolveNamespace(string $contextName): string
    {
        return rtrim($this->namespaceRoot, '\\').'\\'.$contextName;
    }
}
