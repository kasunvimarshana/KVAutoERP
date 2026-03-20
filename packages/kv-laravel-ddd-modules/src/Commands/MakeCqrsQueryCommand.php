<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\CqrsQueryGenerator;
use LaravelDDD\Generators\CqrsQueryHandlerGenerator;

/**
 * Artisan command: generate a CQRS Query and its Handler.
 */
class MakeCqrsQueryCommand extends Command
{
    protected $signature = 'ddd:make-query
                            {context : The bounded context name}
                            {name    : The query base name (e.g. GetProduct)}
                            {--force : Overwrite any existing files}';

    protected $description = 'Create a CQRS Query DTO and its Handler';

    public function __construct(
        protected CqrsQueryGenerator $queryGenerator,
        protected CqrsQueryHandlerGenerator $handlerGenerator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $context = (string) $this->argument('context');
        $name    = (string) $this->argument('name');
        $force   = (bool) $this->option('force');

        $opts = ['context' => $context, 'name' => $name, 'force' => $force];

        $queryCreated   = $this->queryGenerator->generate($opts);
        $handlerCreated = $this->handlerGenerator->generate($opts);

        if (! $queryCreated) {
            $this->warn("Query <comment>{$name}Query</comment> already exists.");
        } else {
            $this->info("✓ Query <comment>{$name}Query</comment> created.");
        }

        if (! $handlerCreated) {
            $this->warn("Handler <comment>{$name}QueryHandler</comment> already exists.");
        } else {
            $this->info("✓ Handler <comment>{$name}QueryHandler</comment> created.");
        }

        return self::SUCCESS;
    }
}
