<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;

class MakeDDDHandlerCommand extends Command
{
    protected $signature = 'make:ddd-handler
                            {module : The module name}
                            {name : The handler name (e.g. CreateOrder)}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a CQRS Handler class within a module';

    public function handle(): int
    {
        $moduleName  = Str::studly($this->argument('module'));
        $handlerName = Str::studly($this->argument('name'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');

        if (! Str::endsWith($handlerName, 'Handler')) {
            $handlerName .= 'Handler';
        }

        $targetPath = "{$modulesPath}/{$moduleName}/Application/Handlers/{$handlerName}.php";
        $files = new Filesystem();

        if ($files->exists($targetPath) && ! $this->option('force')) {
            $this->error("Handler [{$handlerName}] already exists.");
            return self::FAILURE;
        }

        $compiler      = new StubCompiler($files);
        $generator     = new ModuleGenerator($files, $compiler);
        $baseNamespace = "{$namespace}\\{$moduleName}";

        $actionName = Str::replaceLast('Handler', '', $handlerName);
        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            ['{{ module }}' => $actionName]
        );

        $stubPath = __DIR__ . '/../../stubs/CqrsHandler.stub';
        $files->makeDirectory(dirname($targetPath), 0755, true, true);

        $content = $compiler->compile($stubPath, $replacements);
        $files->put($targetPath, $content);

        $this->info("Handler [{$handlerName}] created in module [{$moduleName}].");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
