<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\SpecificationGenerator;

/**
 * Artisan command: generate a Domain Specification.
 */
class MakeSpecificationCommand extends Command
{
    protected $signature = 'ddd:make-specification
                            {context : The bounded context name}
                            {name    : The specification class name}
                            {--force : Overwrite any existing file}';

    protected $description = 'Create a new Domain Specification class';

    public function __construct(protected SpecificationGenerator $generator)
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
            $this->warn("Specification <comment>{$name}</comment> already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("✓ Specification <comment>{$name}</comment> created in context <comment>{$context}</comment>.");

        return self::SUCCESS;
    }
}
