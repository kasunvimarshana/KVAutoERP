<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\ValueObjectGenerator;

/**
 * Artisan command: generate a Domain Value Object.
 */
class MakeValueObjectCommand extends Command
{
    protected $signature = 'ddd:make-value-object
                            {context : The bounded context name}
                            {name    : The value object class name}
                            {--force : Overwrite any existing file}';

    protected $description = 'Create a new Domain Value Object class';

    public function __construct(protected ValueObjectGenerator $generator)
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
            $this->warn("Value object <comment>{$name}</comment> already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("✓ Value Object <comment>{$name}</comment> created in context <comment>{$context}</comment>.");

        return self::SUCCESS;
    }
}
