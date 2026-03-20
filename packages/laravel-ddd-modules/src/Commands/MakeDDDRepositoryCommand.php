<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;

class MakeDDDRepositoryCommand extends Command
{
    protected $signature = 'make:ddd-repository
                            {module : The module name}
                            {name : The repository name (e.g. Order)}
                            {--force : Overwrite if exists}
                            {--interface-only : Only generate the interface, not the Eloquent implementation}
                            {--implementation-only : Only generate the Eloquent implementation}';

    protected $description = 'Generate a Repository Interface and its Eloquent implementation within a DDD module';

    public function handle(): int
    {
        $moduleName  = Str::studly($this->argument('module'));
        $name        = Str::studly($this->argument('name'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');
        $baseNamespace = "{$namespace}\\{$moduleName}";

        $files     = new Filesystem();
        $compiler  = new StubCompiler($files);
        $generator = new ModuleGenerator($files, $compiler);

        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            ['{{ module }}' => $name]
        );

        $stubsDir   = $this->resolveStubsDir($files);
        $generated  = [];
        $exitCode   = self::SUCCESS;

        $interfaceOnly      = $this->option('interface-only');
        $implementationOnly = $this->option('implementation-only');

        // Generate repository interface
        if (! $implementationOnly) {
            $interfacePath = "{$modulesPath}/{$moduleName}/Domain/Repositories/{$name}RepositoryInterface.php";
            $stubFile = "{$stubsDir}/RepositoryInterface.stub";

            if ($files->exists($interfacePath) && ! $this->option('force')) {
                $this->warn("Repository interface [{$name}RepositoryInterface] already exists — skipping.");
            } else {
                $files->makeDirectory(dirname($interfacePath), 0755, true, true);
                $files->put($interfacePath, $compiler->compile($stubFile, $replacements));
                $generated[] = $interfacePath;
                $this->line("  <fg=green>+</> " . str_replace(base_path() . '/', '', $interfacePath));
            }
        }

        // Generate Eloquent implementation
        if (! $interfaceOnly) {
            $implementationPath = "{$modulesPath}/{$moduleName}/Infrastructure/Persistence/Repositories/Eloquent{$name}Repository.php";
            $stubFile = "{$stubsDir}/EloquentRepository.stub";

            if ($files->exists($implementationPath) && ! $this->option('force')) {
                $this->warn("Eloquent repository [Eloquent{$name}Repository] already exists — skipping.");
            } else {
                $files->makeDirectory(dirname($implementationPath), 0755, true, true);
                $files->put($implementationPath, $compiler->compile($stubFile, $replacements));
                $generated[] = $implementationPath;
                $this->line("  <fg=green>+</> " . str_replace(base_path() . '/', '', $implementationPath));
            }
        }

        if (empty($generated)) {
            $this->warn('No files were generated — all targets already exist. Use --force to overwrite.');
            return self::FAILURE;
        }

        $this->info("Repository pair for <comment>[{$name}]</comment> created in module <comment>[{$moduleName}]</comment>.");
        return $exitCode;
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
