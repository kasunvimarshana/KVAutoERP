<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use LaravelDDD\Generators\DtoGenerator;

/**
 * Artisan command: generate a DTO.
 */
class MakeDtoCommand extends Command
{
    protected $signature = 'ddd:make-dto
                            {context : The bounded context name}
                            {name    : The DTO base name (e.g. Product)}
                            {--force : Overwrite any existing file}';

    protected $description = 'Create a new Application DTO class';

    public function __construct(protected DtoGenerator $generator)
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
            $this->warn("DTO <comment>{$name}DTO</comment> already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->info("✓ DTO <comment>{$name}DTO</comment> created in context <comment>{$context}</comment>.");

        return self::SUCCESS;
    }
}
