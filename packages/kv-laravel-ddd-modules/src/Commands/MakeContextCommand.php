<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use LaravelDDD\Generators\ContextGenerator;

/**
 * Artisan command: scaffold a new DDD bounded context.
 */
class MakeContextCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'ddd:make-context
                            {name : The name of the bounded context (PascalCase, e.g. ProductCatalog)}
                            {--force : Overwrite any existing files}';

    /** {@inheritdoc} */
    protected $description = 'Create a new DDD bounded context with full directory structure';

    /**
     * @param  ContextGenerator  $generator
     * @param  Filesystem        $files
     */
    public function __construct(
        protected ContextGenerator $generator,
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $contextName = (string) $this->argument('name');
        $force       = (bool) $this->option('force');

        $this->info("Creating bounded context: <comment>{$contextName}</comment>");

        $created = $this->generator->generate([
            'context' => $contextName,
            'force'   => $force,
        ]);

        if (! $created) {
            $this->warn("Context provider already exists. Use --force to overwrite.");
        } else {
            $this->info("✓ Context <comment>{$contextName}</comment> created successfully.");
        }

        // Scaffold SharedKernel if it does not exist yet
        $basePath        = (string) config('ddd.base_path', 'app');
        $sharedKernelKey = (string) config('ddd.shared_kernel_path', 'SharedKernel');
        $sharedPath      = base_path($basePath.DIRECTORY_SEPARATOR.$sharedKernelKey);

        if (! $this->files->isDirectory($sharedPath)) {
            $this->files->makeDirectory($sharedPath.DIRECTORY_SEPARATOR.'Contracts', 0755, true);
            $this->files->makeDirectory($sharedPath.DIRECTORY_SEPARATOR.'ValueObjects', 0755, true);
            $this->info("✓ SharedKernel scaffolded at <comment>{$sharedPath}</comment>.");
        }

        return self::SUCCESS;
    }
}
