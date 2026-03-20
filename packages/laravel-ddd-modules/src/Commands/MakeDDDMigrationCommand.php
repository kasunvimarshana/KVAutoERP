<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;

class MakeDDDMigrationCommand extends Command
{
    protected $signature = 'make:ddd-migration
                            {module : The module name}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a database migration inside a DDD module';

    public function handle(): int
    {
        $moduleName  = Str::studly($this->argument('module'));
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $namespace   = config('ddd-modules.modules_namespace', 'App\\Modules');

        $files       = new Filesystem();
        $compiler    = new StubCompiler($files);
        $generator   = new ModuleGenerator($files, $compiler);
        $baseNamespace = "{$namespace}\\{$moduleName}";

        $timestamp   = now()->format('Y_m_d_His');
        $tableName   = Str::snake(Str::plural($moduleName));
        $fileName    = "{$timestamp}_create_{$tableName}_table.php";
        $targetDir   = "{$modulesPath}/{$moduleName}/Infrastructure/Persistence/Migrations";
        $targetPath  = "{$targetDir}/{$fileName}";

        $files->makeDirectory($targetDir, 0755, true, true);

        $replacements = $generator->buildReplacements($moduleName, $baseNamespace);

        $stubPath = __DIR__ . '/../../stubs/Migration.stub';
        $content = $compiler->compile($stubPath, $replacements);
        $files->put($targetPath, $content);

        $this->info("Migration [{$fileName}] created for module [{$moduleName}].");
        $this->line("  Path: <comment>{$targetPath}</comment>");

        return self::SUCCESS;
    }
}
