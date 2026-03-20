<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaravelDddModules\Generators\ModuleGenerator;
use LaravelDddModules\Generators\StubCompiler;

class MakeDDDModuleCommand extends Command
{
    protected $signature = 'make:ddd-module
                            {name : The name of the DDD module (e.g. Order, User, Billing)}
                            {--force : Overwrite existing files}
                            {--without-stubs : Only create directories, no stub files}
                            {--only= : Comma-separated layers to generate (Domain,Application,Infrastructure,Presentation)}';

    protected $description = 'Generate a complete DDD module structure with all layers';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! preg_match('/^[A-Za-z][A-Za-z0-9]*$/', Str::studly($name))) {
            $this->error("Invalid module name: [{$name}]. Use only letters and numbers.");
            return self::FAILURE;
        }

        $moduleName = Str::studly($name);
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $modulePath = "{$modulesPath}/{$moduleName}";

        $files = new Filesystem();

        if ($files->isDirectory($modulePath) && ! $this->option('force')) {
            $this->error("Module [{$moduleName}] already exists at [{$modulePath}].");
            $this->line('  Use <info>--force</info> to overwrite.');
            return self::FAILURE;
        }

        $this->info("Creating DDD module: <comment>{$moduleName}</comment>");
        $this->newLine();

        $generator = new ModuleGenerator($files, new StubCompiler($files));

        // Filter structure if --only is provided
        $onlyLayers = $this->option('only')
            ? array_map('trim', explode(',', $this->option('only')))
            : null;

        if ($onlyLayers) {
            $this->filterStructure($onlyLayers);
        }

        $options = [
            'force'         => $this->option('force'),
            'without_stubs' => $this->option('without-stubs'),
        ];

        $result = $generator->generate($moduleName, $options);

        $this->displayResults($result);

        $this->newLine();
        $this->info("✓ Module <comment>{$moduleName}</comment> created successfully!");
        $this->newLine();
        $this->line("  Path:      <comment>{$result['path']}</comment>");
        $this->line("  Namespace: <comment>{$result['namespace']}</comment>");
        $this->newLine();
        $this->line("  Don't forget to add the module namespace to your <info>composer.json</info>:");
        $this->line("  <comment>\"App\\\\Modules\\\\\": \"app/Modules/\"</comment>");

        return self::SUCCESS;
    }

    protected function filterStructure(array $onlyLayers): void
    {
        $structure = config('ddd-modules.structure', []);
        $filtered = array_filter($structure, fn($key) => in_array($key, $onlyLayers), ARRAY_FILTER_USE_KEY);
        config(['ddd-modules.structure' => $filtered]);

        // Disable stubs for layers that are not included
        $layerStubKeys = [
            'Domain'         => ['entity', 'value_object', 'aggregate', 'repository_interface', 'domain_service', 'domain_event', 'domain_exception', 'domain_enum', 'domain_policy', 'specification'],
            'Application'    => ['use_case', 'dto', 'mapper', 'validator', 'cqrs_command', 'cqrs_query', 'cqrs_handler', 'application_exception'],
            'Infrastructure' => ['provider', 'eloquent_model', 'eloquent_repository', 'migration', 'factory', 'seeder', 'job', 'listener', 'notification', 'cast'],
            'Presentation'   => ['api_controller', 'web_controller', 'form_request', 'api_resource', 'api_routes', 'web_routes', 'console_command', 'middleware'],
        ];

        $stubsGenerate = config('ddd-modules.stubs.generate', []);
        foreach ($layerStubKeys as $layer => $stubKeys) {
            if (! in_array($layer, $onlyLayers)) {
                foreach ($stubKeys as $stubKey) {
                    $stubsGenerate[$stubKey] = false;
                }
            }
        }
        config(['ddd-modules.stubs.generate' => $stubsGenerate]);
    }

    protected function displayResults(array $result): void
    {
        $this->line('  <fg=green>DIRECTORIES CREATED:</>');
        foreach ($result['directories'] as $dir) {
            $relative = str_replace(base_path() . '/', '', $dir);
            $this->line("    <fg=gray>+</> {$relative}");
        }

        if (! empty($result['files'])) {
            $this->newLine();
            $this->line('  <fg=green>FILES GENERATED:</>');
            foreach ($result['files'] as $file) {
                $relative = str_replace(base_path() . '/', '', $file);
                $this->line("    <fg=gray>+</> {$relative}");
            }
        }
    }
}
