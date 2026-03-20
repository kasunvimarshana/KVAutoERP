<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Contracts\ContextRegistrar;

/**
 * Artisan command: display package information and configuration.
 */
class DddInfoCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'ddd:info';

    /** {@inheritdoc} */
    protected $description = 'Display DDD package information, configuration, and available commands';

    public function __construct(protected ContextRegistrar $registrar)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->line('');
        $this->line('<fg=blue>╔══════════════════════════════════════════════╗</>');
        $this->line('<fg=blue>║     Laravel DDD Modules Package              ║</>');
        $this->line('<fg=blue>║     by Kasun Vimarshana                      ║</>');
        $this->line('<fg=blue>╚══════════════════════════════════════════════╝</>');
        $this->line('');

        $this->info('📦 Package: <comment>kasunvimarshana/kv-laravel-ddd-modules</comment>');
        $this->line('');

        // Configuration
        $this->info('⚙  Configuration:');
        $this->table(
            ['Key', 'Value'],
            [
                ['namespace_root',        config('ddd.namespace_root', 'App')],
                ['base_path',             config('ddd.base_path', 'app')],
                ['architecture_mode',     config('ddd.architecture_mode', 'ddd')],
                ['shared_kernel_path',    config('ddd.shared_kernel_path', 'SharedKernel')],
                ['auto_discover_contexts', config('ddd.auto_discover_contexts') ? 'true' : 'false'],
                ['auto_register_providers', config('ddd.auto_register_providers') ? 'true' : 'false'],
                ['stubs_path',            config('ddd.stubs_path') ?? '(package default)'],
            ],
        );

        // Registered contexts
        $basePath = base_path((string) config('ddd.base_path', 'app'));
        $this->registrar->discover($basePath);
        $contexts = $this->registrar->all();

        $this->line('');
        $this->info('🗂  Registered Contexts: <comment>'.count($contexts).'</comment>');

        // Available commands
        $this->line('');
        $this->info('🛠  Available Commands:');
        $commands = [
            ['ddd:make-context',       'Create a new DDD bounded context'],
            ['ddd:make-entity',        'Create a Domain Entity'],
            ['ddd:make-value-object',  'Create a Domain Value Object'],
            ['ddd:make-aggregate',     'Create an Aggregate Root'],
            ['ddd:make-event',         'Create a Domain Event'],
            ['ddd:make-service',       'Create a Domain Service'],
            ['ddd:make-specification', 'Create a Domain Specification'],
            ['ddd:make-repository',    'Create a Repository interface + implementation'],
            ['ddd:make-command',       'Create a CQRS Command + Handler'],
            ['ddd:make-query',         'Create a CQRS Query + Handler'],
            ['ddd:make-dto',           'Create an Application DTO'],
            ['ddd:list-contexts',      'List all discovered contexts'],
            ['ddd:publish-stubs',      'Publish stubs to your application'],
            ['ddd:info',               'Show this information'],
        ];
        $this->table(['Command', 'Description'], $commands);

        return self::SUCCESS;
    }
}
