<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class DddInfoCommand extends Command
{
    protected $signature = 'ddd:info';

    protected $description = 'Display DDD Modules package information, configuration summary, and discovered modules';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>Laravel DDD Modules</> — Zero-dependency DDD scaffolding for Laravel');
        $this->newLine();

        $this->displayConfig();
        $this->newLine();
        $this->displayModules();
        $this->newLine();
        $this->displayCommands();
        $this->newLine();

        return self::SUCCESS;
    }

    protected function displayConfig(): void
    {
        $this->line('  <fg=yellow;options=bold>Configuration</>');
        $this->line('  ' . str_repeat('─', 60));

        $rows = [
            ['Modules Path',      config('ddd-modules.modules_path')],
            ['Modules Namespace', config('ddd-modules.modules_namespace')],
            ['Shared Module',     config('ddd-modules.shared_module', 'Shared')],
            ['Auto-Discover',     config('ddd-modules.auto_discover', true) ? '<fg=green>enabled</>' : '<fg=red>disabled</>'],
            ['Custom Stubs Path', config('ddd-modules.stubs.path') ?: '<fg=gray>default (package stubs)</>'],
        ];

        foreach ($rows as [$label, $value]) {
            $this->line(sprintf("  <fg=gray>%-22s</> %s", $label, $value));
        }

        // Stub generation summary
        $stubsGenerate = config('ddd-modules.stubs.generate', []);
        $enabled  = count(array_filter($stubsGenerate));
        $total    = count($stubsGenerate);
        $this->line(sprintf("  <fg=gray>%-22s</> %d / %d enabled", 'Stubs Generation', $enabled, $total));
    }

    protected function displayModules(): void
    {
        $this->line('  <fg=yellow;options=bold>Discovered Modules</>');
        $this->line('  ' . str_repeat('─', 60));

        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $files = new Filesystem();

        if (! $files->isDirectory($modulesPath)) {
            $this->line('  <fg=gray>No modules directory found at [' . $modulesPath . ']</>');
            return;
        }

        $dirs = $files->directories($modulesPath);

        if (empty($dirs)) {
            $this->line('  <fg=gray>No modules found.</>');
            return;
        }

        $headers = ['Module', 'Path', 'ServiceProvider'];
        $rows    = [];

        $modulesNamespace = config('ddd-modules.modules_namespace', 'App\\Modules');

        foreach ($dirs as $dir) {
            $name     = basename($dir);
            $provider = "{$modulesNamespace}\\{$name}\\Infrastructure\\Providers\\{$name}ServiceProvider";
            $hasProvider = class_exists($provider) ? '<fg=green>registered</>' : '<fg=gray>not loaded</>';

            $rows[] = [$name, str_replace(base_path() . '/', '', $dir), $hasProvider];
        }

        $this->table($headers, $rows);
    }

    protected function displayCommands(): void
    {
        $this->line('  <fg=yellow;options=bold>Available Commands</>');
        $this->line('  ' . str_repeat('─', 60));

        $commands = [
            ['make:ddd-module {name}',          'Scaffold complete module (all 4 DDD layers)'],
            ['make:ddd-shared',                 'Scaffold Shared cross-cutting kernel'],
            ['make:ddd-entity {m} {n}',         'Generate domain entity'],
            ['make:ddd-value-object {m} {n}',   'Generate value object'],
            ['make:ddd-aggregate {m} {n}',      'Generate aggregate root'],
            ['make:ddd-domain-event {m} {n}',   'Generate domain event'],
            ['make:ddd-domain-service {m} {n}', 'Generate domain service'],
            ['make:ddd-specification {m} {n}',  'Generate domain specification'],
            ['make:ddd-repository {m} {n}',     'Generate repository interface + Eloquent impl'],
            ['make:ddd-dto {m} {n}',            'Generate DTO'],
            ['make:ddd-use-case {m} {n}',       'Generate application use case'],
            ['make:ddd-command {m} {n}',        'Generate CQRS command'],
            ['make:ddd-query {m} {n}',          'Generate CQRS query'],
            ['make:ddd-handler {m} {n}',        'Generate CQRS handler'],
            ['make:ddd-migration {m}',          'Generate module migration'],
            ['ddd:list-modules',                'List all discovered modules'],
            ['ddd:info',                        'Show package info and config'],
            ['ddd:publish',                     'Publish stubs and/or config'],
        ];

        $this->table(['Command', 'Purpose'], $commands);
    }
}
