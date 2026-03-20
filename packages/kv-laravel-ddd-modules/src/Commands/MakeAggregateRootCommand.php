<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\AggregateRootGenerator;

/**
 * Artisan command: generate an Aggregate Root.
 */
class MakeAggregateRootCommand extends Command
{
    protected $signature = 'ddd:make-aggregate
                            {context : The bounded context name}
                            {name    : The aggregate root class name}
                            {--force : Overwrite any existing file}';

    protected $description = 'Create a new Domain Aggregate Root class';

    public function __construct(protected AggregateRootGenerator $generator)
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
            $this->warn("Aggregate root <comment>{$name}</comment> already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("✓ Aggregate Root <comment>{$name}</comment> created in context <comment>{$context}</comment>.");

        return self::SUCCESS;
    }
}
