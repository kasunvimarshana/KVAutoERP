<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\StubCompiler;
use LaravelDddModules\Generators\ModuleGenerator;

class MakeDDDValueObjectCommand extends Command
{
    protected $signature = 'make:ddd-value-object
                            {module : The module name}
                            {name : The value object name}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a DDD Value Object class within a module';

    public function handle(): int
    {
        $moduleName  = Str::studly($this->argument('module'));
        $voName      = Str::studly($this->argument('name'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');

        $targetPath = "{$modulesPath}/{$moduleName}/Domain/ValueObjects/{$voName}.php";

        $files = new Filesystem();

        if ($files->exists($targetPath) && ! $this->option('force')) {
            $this->error("Value Object [{$voName}] already exists.");
            return self::FAILURE;
        }

        $compiler      = new StubCompiler($files);
        $generator     = new ModuleGenerator($files, $compiler);
        $baseNamespace = config('ddd-modules.modules_namespace', 'App\\Modules') . "\\{$moduleName}";

        $replacements = array_merge(
            $generator->buildReplacements($moduleName, $baseNamespace),
            [
                '{{ valueObject }}'      => $voName,
                '{{ valueObjectLower }}' => Str::lower($voName),
                '{{ valueObjectSnake }}' => Str::snake($voName),
            ]
        );

        $stubPath = __DIR__ . '/../../stubs/ValueObject.stub';
        $files->makeDirectory(dirname($targetPath), 0755, true, true);
        $files->put($targetPath, $compiler->compile($stubPath, $replacements));

        $this->info("Value Object [{$voName}] created in module [{$moduleName}].");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
