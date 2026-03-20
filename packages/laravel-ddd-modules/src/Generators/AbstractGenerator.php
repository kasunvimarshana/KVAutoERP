<?php

namespace LaravelDddModules\Generators;

use Illuminate\Filesystem\Filesystem;

/**
 * Abstract base generator.
 * Extend this class to add new generator types without modifying the package core.
 */
abstract class AbstractGenerator
{
    public function __construct(
        protected Filesystem $files,
        protected StubCompiler $stubCompiler
    ) {}

    /**
     * Execute the generator. Returns result metadata.
     *
     * @return array{directories: string[], files: string[]}
     */
    abstract public function generate(string $name, array $options = []): array;

    /**
     * Build the placeholder replacement map for stub compilation.
     *
     * @return array<string, string>
     */
    abstract public function buildReplacements(string $name, string $namespace): array;

    /**
     * Resolve the package stubs directory, preferring published/custom stubs.
     */
    protected function resolveStubPath(): string
    {
        $customPath = config('ddd-modules.stubs.path');

        if ($customPath && $this->files->isDirectory($customPath)) {
            return $customPath;
        }

        $publishedPath = base_path('stubs/ddd-modules');
        if ($this->files->isDirectory($publishedPath)) {
            return $publishedPath;
        }

        return __DIR__ . '/../../stubs';
    }

    /**
     * Write a compiled stub to its target path.
     * Creates intermediate directories as needed. Skips if target exists and $force is false.
     */
    protected function writeStub(string $stubFile, string $targetPath, array $replacements, bool $force = false): bool
    {
        if (! $this->files->exists($stubFile)) {
            return false;
        }

        $targetDir = dirname($targetPath);
        if (! $this->files->isDirectory($targetDir)) {
            $this->files->makeDirectory($targetDir, 0755, true, true);
        }

        if (! $force && $this->files->exists($targetPath)) {
            return false;
        }

        $compiled = $this->stubCompiler->compile($stubFile, $replacements);
        $this->files->put($targetPath, $compiled);
        return true;
    }

    /**
     * Ensure a directory exists and optionally place a .gitkeep inside it.
     */
    protected function ensureDirectory(string $path, bool $gitkeep = true): void
    {
        $this->files->makeDirectory($path, 0755, true, true);

        if ($gitkeep) {
            $keep = "{$path}/.gitkeep";
            if (! $this->files->exists($keep)) {
                $this->files->put($keep, '');
            }
        }
    }
}
