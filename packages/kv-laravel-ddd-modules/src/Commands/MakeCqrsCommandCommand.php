<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\CqrsCommandGenerator;
use LaravelDDD\Generators\CqrsCommandHandlerGenerator;

/**
 * Artisan command: generate a CQRS Command and its Handler.
 */
class MakeCqrsCommandCommand extends Command
{
    protected $signature = 'ddd:make-command
                            {context : The bounded context name}
                            {name    : The command base name (e.g. CreateProduct)}
                            {--force : Overwrite any existing files}';

    protected $description = 'Create a CQRS Command DTO and its Handler';

    public function __construct(
        protected CqrsCommandGenerator $commandGenerator,
        protected CqrsCommandHandlerGenerator $handlerGenerator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $context = (string) $this->argument('context');
        $name    = (string) $this->argument('name');
        $force   = (bool) $this->option('force');

        $opts = ['context' => $context, 'name' => $name, 'force' => $force];

        $commandCreated = $this->commandGenerator->generate($opts);
        $handlerCreated = $this->handlerGenerator->generate($opts);

        if (! $commandCreated) {
            $this->warn("Command <comment>{$name}Command</comment> already exists.");
        } else {
            $this->info("✓ Command <comment>{$name}Command</comment> created.");
        }

        if (! $handlerCreated) {
            $this->warn("Handler <comment>{$name}CommandHandler</comment> already exists.");
        } else {
            $this->info("✓ Handler <comment>{$name}CommandHandler</comment> created.");
        }

        return self::SUCCESS;
    }
}
