<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;

class MakeDDDCqrsQueryCommand extends Command
{
    protected $signature = 'make:ddd-query
                            {module : The module name}
                            {name : The query name (e.g. GetOrder)}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a CQRS Query class within a module';

    public function handle(): int
    {
        $moduleName = Str::studly($this->argument('module'));
        $queryName  = Str::studly($this->argument('name'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');

        if (! Str::endsWith($queryName, 'Query')) {
            $queryName .= 'Query';
        }

        $targetPath = "{$modulesPath}/{$moduleName}/Application/Queries/{$queryName}.php";
        $files = new Filesystem();

        if ($files->exists($targetPath) && ! $this->option('force')) {
            $this->error("CQRS Query [{$queryName}] already exists.");
            return self::FAILURE;
        }

        $compiler      = new StubCompiler($files);
        $generator     = new ModuleGenerator($files, $compiler);
        $baseNamespace = "{$namespace}\\{$moduleName}";

        $actionName = Str::replaceLast('Query', '', $queryName);
        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            ['{{ module }}' => $actionName]
        );

        $stubPath = __DIR__ . '/../../stubs/CqrsQuery.stub';
        $files->makeDirectory(dirname($targetPath), 0755, true, true);

        $content = $compiler->compile($stubPath, $replacements);
        $files->put($targetPath, $content);

        $this->info("CQRS Query [{$queryName}] created in module [{$moduleName}].");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
