<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;

class MakeDDDCqrsCommandCommand extends Command
{
    protected $signature = 'make:ddd-command
                            {module : The module name}
                            {name : The command name (e.g. CreateOrder)}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a CQRS Command class within a module';

    public function handle(): int
    {
        $moduleName  = Str::studly($this->argument('module'));
        $commandName = Str::studly($this->argument('name'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');

        if (! Str::endsWith($commandName, 'Command')) {
            $commandName .= 'Command';
        }

        $targetPath = "{$modulesPath}/{$moduleName}/Application/Commands/{$commandName}.php";
        $files = new Filesystem();

        if ($files->exists($targetPath) && ! $this->option('force')) {
            $this->error("CQRS Command [{$commandName}] already exists.");
            return self::FAILURE;
        }

        $compiler      = new StubCompiler($files);
        $generator     = new ModuleGenerator($files, $compiler);
        $baseNamespace = "{$namespace}\\{$moduleName}";

        $actionName = Str::replaceLast('Command', '', $commandName);
        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            ['{{ module }}' => $actionName]
        );

        $stubPath = __DIR__ . '/../../stubs/CqrsCommand.stub';
        $files->makeDirectory(dirname($targetPath), 0755, true, true);

        $content = $compiler->compile($stubPath, $replacements);
        $files->put($targetPath, $content);

        $this->info("CQRS Command [{$commandName}] created in module [{$moduleName}].");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
