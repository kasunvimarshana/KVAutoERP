<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\EntityGenerator;

/**
 * Artisan command: generate a Domain Entity.
 */
class MakeEntityCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'ddd:make-entity
                            {context : The bounded context name}
                            {name    : The entity class name}
                            {--force : Overwrite any existing file}';

    /** {@inheritdoc} */
    protected $description = 'Create a new Domain Entity class';

    public function __construct(protected EntityGenerator $generator)
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
        $context = (string) $this->argument('context');
        $name    = (string) $this->argument('name');
        $force   = (bool) $this->option('force');

        $created = $this->generator->generate([
            'context' => $context,
            'name'    => $name,
            'force'   => $force,
        ]);

        if (! $created) {
            $this->warn("Entity <comment>{$name}</comment> already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("✓ Entity <comment>{$name}</comment> created in context <comment>{$context}</comment>.");

        return self::SUCCESS;
    }
}
