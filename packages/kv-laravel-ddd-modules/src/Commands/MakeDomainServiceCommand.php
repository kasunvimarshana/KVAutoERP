<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\DomainServiceGenerator;

/**
 * Artisan command: generate a Domain Service.
 */
class MakeDomainServiceCommand extends Command
{
    protected $signature = 'ddd:make-service
                            {context : The bounded context name}
                            {name    : The domain service class name}
                            {--force : Overwrite any existing file}';

    protected $description = 'Create a new Domain Service class';

    public function __construct(protected DomainServiceGenerator $generator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $context = (string) $this->argument('context');
        $name    = (string) $this->argument('name');
        $force   = (bool) $this->option('force');

        $created = $this->generator->generate([
            'context' => $context,
            'name'    => $name,
            'force'   => $force,
        ]);

        if (! $created) {
            $this->warn("Domain service <comment>{$name}</comment> already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("✓ Domain Service <comment>{$name}</comment> created in context <comment>{$context}</comment>.");

        return self::SUCCESS;
    }
}
