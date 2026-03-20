<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;

/**
 * Abstract base command for all DDD make:* commands.
 * Extend this class to add new single-file generator commands without modifying the package core.
 */
abstract class AbstractDddCommand extends Command
{
    /**
     * Return the stub filename (relative to the stubs directory) for this generator.
     */
    abstract protected function stubName(): string;

    /**
     * Return the target file path (absolute) for the given module and artifact name.
     */
    abstract protected function targetPath(string $modulesPath, string $moduleName, string $artifactName): string;

    /**
     * Human-readable description of what's being generated (e.g., "Entity", "Value Object").
     */
    abstract protected function artifactLabel(): string;

    /**
     * Resolve and return the filesystem instance.
     */
    protected function filesystem(): Filesystem
    {
        return new Filesystem();
    }

    /**
     * Resolve and return the stub compiler.
     */
    protected function compiler(Filesystem $files): StubCompiler
    {
        return new StubCompiler($files);
    }

    /**
     * Resolve the stubs directory path.
     */
    protected function resolveStubsDir(Filesystem $files): string
    {
        $customPath = config('ddd-modules.stubs.path');
        if ($customPath && $files->isDirectory($customPath)) {
            return $customPath;
        }

        $publishedPath = base_path('stubs/ddd-modules');
        if ($files->isDirectory($publishedPath)) {
            return $publishedPath;
        }

        return __DIR__ . '/../../stubs';
    }

    /**
     * Common generation logic: resolves paths, checks existence, compiles, writes.
     */
    protected function generateSingle(
        string $moduleName,
        string $artifactName,
        array $extraReplacements = []
    ): int {
        $moduleName    = Str::studly($moduleName);
        $artifactName  = Str::studly($artifactName);
        $modulesPath   = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace     = config('ddd-modules.modules_namespace', 'App\\Modules');
        $baseNamespace = "{$namespace}\\{$moduleName}";

        $files         = $this->filesystem();
        $compiler      = $this->compiler($files);
        $generator     = new ModuleGenerator($files, $compiler);

        $targetPath = $this->targetPath($modulesPath, $moduleName, $artifactName);

        if ($files->exists($targetPath) && ! $this->option('force')) {
            $this->error("{$this->artifactLabel()} [{$artifactName}] already exists.");
            return self::FAILURE;
        }

        $stubsDir = $this->resolveStubsDir($files);
        $stubFile = "{$stubsDir}/{$this->stubName()}";

        if (! $files->exists($stubFile)) {
            $this->error("Stub [{$this->stubName()}] not found at [{$stubFile}].");
            return self::FAILURE;
        }

        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            ['{{ name }}' => $artifactName],
            $extraReplacements
        );

        $files->makeDirectory(dirname($targetPath), 0755, true, true);
        $content = $compiler->compile($stubFile, $replacements);
        $files->put($targetPath, $content);

        $this->info("{$this->artifactLabel()} <comment>[{$artifactName}]</comment> created in module <comment>[{$moduleName}]</comment>.");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
