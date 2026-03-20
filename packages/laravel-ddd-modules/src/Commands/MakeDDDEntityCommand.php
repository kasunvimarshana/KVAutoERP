<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\StubCompiler;
use LaravelDddModules\Generators\ModuleGenerator;

class MakeDDDEntityCommand extends Command
{
    protected $signature = 'make:ddd-entity
                            {module : The module name}
                            {name : The entity name}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a DDD Entity class within a module';

    public function handle(): int
    {
        $moduleName  = Str::studly($this->argument('module'));
        $entityName  = Str::studly($this->argument('name'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');

        $targetPath = "{$modulesPath}/{$moduleName}/Domain/Entities/{$entityName}.php";

        if ((new Filesystem())->exists($targetPath) && ! $this->option('force')) {
            $this->error("Entity [{$entityName}] already exists.");
            return self::FAILURE;
        }

        $files    = new Filesystem();
        $compiler = new StubCompiler($files);
        $generator = new ModuleGenerator($files, $compiler);

        $baseNamespace = "{$namespace}\\{$moduleName}";
        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            [
                '{{ entity }}'      => $entityName,
                '{{ entityLower }}' => Str::lower($entityName),
                '{{ entitySnake }}' => Str::snake($entityName),
            ]
        );

        $stubPath = __DIR__ . '/../../stubs/Entity.stub';
        $files->makeDirectory(dirname($targetPath), 0755, true, true);
        $files->put($targetPath, $compiler->compile($stubPath, $replacements));

        $this->info("Entity [{$entityName}] created in module [{$moduleName}].");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
