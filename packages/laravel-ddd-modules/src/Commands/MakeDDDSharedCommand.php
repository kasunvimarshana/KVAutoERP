<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use LaravelDddModules\Generators\StubCompiler;

class MakeDDDSharedCommand extends Command
{
    protected $signature = 'make:ddd-shared
                            {--force : Overwrite existing files}';

    protected $description = 'Scaffold the Shared cross-cutting module with base contracts and common components';

    public function handle(): int
    {
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $modulesNamespace = config('ddd-modules.modules_namespace', 'App\\Modules');
        $sharedName = config('ddd-modules.shared_module', 'Shared');

        $basePath = "{$modulesPath}/{$sharedName}";
        $baseNamespace = "{$modulesNamespace}\\{$sharedName}";

        $files = new Filesystem();

        if ($files->isDirectory($basePath) && ! $this->option('force')) {
            $this->error("Shared module already exists at [{$basePath}].");
            $this->line('  Use <info>--force</info> to overwrite.');
            return self::FAILURE;
        }

        $this->info("Creating <comment>Shared</comment> cross-cutting module...");
        $this->newLine();

        $sharedStructure = [
            'Domain/Contracts',
            'Domain/ValueObjects',
            'Domain/Exceptions',
            'Domain/Traits',
            'Domain/Events',
            'Application/Contracts',
            'Application/DTOs',
            'Application/Exceptions',
            'Application/Traits',
            'Infrastructure/Concerns',
        ];

        $createdDirs = [];
        foreach ($sharedStructure as $subfolder) {
            $fullPath = "{$basePath}/{$subfolder}";
            $files->makeDirectory($fullPath, 0755, true, true);
            $createdDirs[] = $fullPath;
            $gitkeep = "{$fullPath}/.gitkeep";
            if (! $files->exists($gitkeep)) {
                $files->put($gitkeep, '');
            }
        }

        $compiler = new StubCompiler($files);
        $stubsDir = $this->resolveStubsDir($files);

        $contractsMap = [
            'AggregateRootContract.stub' => "{$basePath}/Domain/Contracts/AggregateRootInterface.php",
            'EntityContract.stub'        => "{$basePath}/Domain/Contracts/EntityInterface.php",
            'RepositoryContract.stub'    => "{$basePath}/Domain/Contracts/RepositoryInterface.php",
            'DomainEventContract.stub'   => "{$basePath}/Domain/Contracts/DomainEventInterface.php",
            'ValueObjectContract.stub'   => "{$basePath}/Domain/Contracts/ValueObjectInterface.php",
        ];

        $replacements = [
            '{{ namespace }}'   => $baseNamespace,
            '{{ module }}'      => $sharedName,
            '{{ moduleLower }}' => strtolower($sharedName),
        ];

        $createdFiles = [];
        foreach ($contractsMap as $stubName => $targetPath) {
            $stubFile = "{$stubsDir}/Contracts/{$stubName}";
            if (! $files->exists($stubFile)) {
                continue;
            }
            if (! $files->exists($targetPath) || $this->option('force')) {
                $files->put($targetPath, $compiler->compile($stubFile, $replacements));
                $createdFiles[] = $targetPath;
            }
        }

        // Generate Shared Kernel production value objects
        $sharedKernelStubs = [
            'Uuid.stub'   => "{$basePath}/Domain/ValueObjects/Uuid.php",
            'Email.stub'  => "{$basePath}/Domain/ValueObjects/Email.php",
            'Money.stub'  => "{$basePath}/Domain/ValueObjects/Money.php",
        ];

        foreach ($sharedKernelStubs as $stubName => $targetPath) {
            $stubFile = "{$stubsDir}/SharedKernel/{$stubName}";
            if (! $files->exists($stubFile)) {
                continue;
            }
            if (! $files->exists($targetPath) || $this->option('force')) {
                $files->put($targetPath, $compiler->compile($stubFile, $replacements));
                $createdFiles[] = $targetPath;
            }
        }

        $this->line('  <fg=green>DIRECTORIES CREATED:</>');
        foreach ($createdDirs as $dir) {
            $relative = str_replace(base_path() . '/', '', $dir);
            $this->line("    <fg=gray>+</> {$relative}");
        }

        if (! empty($createdFiles)) {
            $this->newLine();
            $this->line('  <fg=green>FILES GENERATED:</>');
            foreach ($createdFiles as $file) {
                $relative = str_replace(base_path() . '/', '', $file);
                $this->line("    <fg=gray>+</> {$relative}");
            }
        }

        $this->newLine();
        $this->info("✓ Shared module created at <comment>{$basePath}</comment>");
        $this->newLine();
        $this->line("  Namespace: <comment>{$baseNamespace}</comment>");

        return self::SUCCESS;
    }

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
}
