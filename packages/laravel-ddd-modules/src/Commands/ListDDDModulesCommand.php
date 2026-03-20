<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ListDDDModulesCommand extends Command
{
    protected $signature = 'ddd:list-modules';

    protected $description = 'List all DDD modules in the application';

    public function handle(): int
    {
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $files = new Filesystem();

        if (! $files->isDirectory($modulesPath)) {
            $this->warn('No modules directory found at: ' . $modulesPath);
            return self::SUCCESS;
        }

        $modules = $files->directories($modulesPath);

        if (empty($modules)) {
            $this->warn('No DDD modules found.');
            return self::SUCCESS;
        }

        $this->info('DDD Modules:');
        $this->newLine();

        $rows = [];
        foreach ($modules as $module) {
            $name = basename($module);
            $providerExists = $files->exists("{$module}/Infrastructure/Providers/{$name}ServiceProvider.php");
            $namespace = config('ddd-modules.modules_namespace', 'App\\Modules') . "\\{$name}";
            $layers = array_map('basename', $files->directories($module));
            $rows[] = [
                $name,
                $namespace,
                implode(', ', $layers),
                $providerExists ? '<info>Yes</info>' : '<comment>No</comment>',
            ];
        }

        $this->table(['Module', 'Namespace', 'Layers', 'Provider'], $rows);

        return self::SUCCESS;
    }
}
