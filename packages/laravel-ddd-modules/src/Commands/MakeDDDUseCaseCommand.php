<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\StubCompiler;
use LaravelDddModules\Generators\ModuleGenerator;

class MakeDDDUseCaseCommand extends Command
{
    protected $signature = 'make:ddd-use-case
                            {module : The module name}
                            {name : The use case name (e.g. CreateOrder)}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a DDD Use Case class within a module';

    public function handle(): int
    {
        $moduleName  = Str::studly($this->argument('module'));
        $useCaseName = Str::studly($this->argument('name'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');

        // Append UseCase suffix if not already present
        if (! Str::endsWith($useCaseName, 'UseCase')) {
            $useCaseName .= 'UseCase';
        }

        $targetPath = "{$modulesPath}/{$moduleName}/Application/UseCases/{$useCaseName}.php";

        $files = new Filesystem();

        if ($files->exists($targetPath) && ! $this->option('force')) {
            $this->error("Use Case [{$useCaseName}] already exists.");
            return self::FAILURE;
        }

        $compiler      = new StubCompiler($files);
        $generator     = new ModuleGenerator($files, $compiler);
        $baseNamespace = "{$namespace}\\{$moduleName}";

        $actionName = Str::replaceLast('UseCase', '', $useCaseName);

        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            [
                '{{ useCase }}'     => $useCaseName,
                '{{ action }}'      => $actionName,
                '{{ actionLower }}' => Str::camel($actionName),
            ]
        );

        $stubPath = __DIR__ . '/../../stubs/UseCase.stub';
        $files->makeDirectory(dirname($targetPath), 0755, true, true);
        $files->put($targetPath, $compiler->compile($stubPath, $replacements));

        $this->info("Use Case [{$useCaseName}] created in module [{$moduleName}].");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
