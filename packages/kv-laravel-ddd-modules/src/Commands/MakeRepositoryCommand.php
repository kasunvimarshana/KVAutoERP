<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\EloquentRepositoryGenerator;
use LaravelDDD\Generators\RepositoryInterfaceGenerator;

/**
 * Artisan command: generate a Repository interface and its Eloquent implementation.
 */
class MakeRepositoryCommand extends Command
{
    protected $signature = 'ddd:make-repository
                            {context : The bounded context name}
                            {name    : The repository base name (e.g. Product)}
                            {--force : Overwrite any existing files}';

    protected $description = 'Create a Repository interface and Eloquent implementation';

    public function __construct(
        protected RepositoryInterfaceGenerator $interfaceGenerator,
        protected EloquentRepositoryGenerator $eloquentGenerator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $context = (string) $this->argument('context');
        $name    = (string) $this->argument('name');
        $force   = (bool) $this->option('force');

        $opts = ['context' => $context, 'name' => $name, 'force' => $force];

        $interfaceCreated = $this->interfaceGenerator->generate($opts);
        $eloquentCreated  = $this->eloquentGenerator->generate($opts);

        if (! $interfaceCreated) {
            $this->warn("Repository interface for <comment>{$name}</comment> already exists.");
        } else {
            $this->info("✓ Repository interface <comment>{$name}RepositoryInterface</comment> created.");
        }

        if (! $eloquentCreated) {
            $this->warn("Eloquent repository for <comment>{$name}</comment> already exists.");
        } else {
            $this->info("✓ Eloquent repository <comment>Eloquent{$name}Repository</comment> created.");
        }

        return self::SUCCESS;
    }
}
