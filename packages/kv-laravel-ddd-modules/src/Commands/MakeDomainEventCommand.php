<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\DomainEventGenerator;

/**
 * Artisan command: generate a Domain Event.
 */
class MakeDomainEventCommand extends Command
{
    protected $signature = 'ddd:make-event
                            {context : The bounded context name}
                            {name    : The domain event class name}
                            {--force : Overwrite any existing file}';

    protected $description = 'Create a new Domain Event class';

    public function __construct(protected DomainEventGenerator $generator)
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
            $this->warn("Domain event <comment>{$name}</comment> already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("✓ Domain Event <comment>{$name}</comment> created in context <comment>{$context}</comment>.");

        return self::SUCCESS;
    }
}
