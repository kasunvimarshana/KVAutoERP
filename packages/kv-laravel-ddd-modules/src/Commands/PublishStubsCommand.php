<?php

declare(strict_types=1);

namespace LaravelDDD\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Artisan command: publish package stubs to the application's stubs directory.
 */
class PublishStubsCommand extends Command
{
    /** {@inheritdoc} */
    protected $signature = 'ddd:publish-stubs
                            {--force : Overwrite any existing stubs}';

    /** {@inheritdoc} */
    protected $description = 'Publish DDD package stubs to stubs/ddd/ in your application';

    public function __construct(protected Filesystem $files)
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
        $packageStubsPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'stubs';
        $targetPath       = base_path('stubs'.DIRECTORY_SEPARATOR.'ddd');
        $force            = (bool) $this->option('force');

        if (! $this->files->isDirectory($packageStubsPath)) {
            $this->error("Package stubs directory not found: {$packageStubsPath}");

            return self::FAILURE;
        }

        if (! $this->files->isDirectory($targetPath)) {
            $this->files->makeDirectory($targetPath, 0755, true);
        }

        $stubs = $this->files->files($packageStubsPath);
        $count = 0;

        foreach ($stubs as $stub) {
            $destination = $targetPath.DIRECTORY_SEPARATOR.$stub->getFilename();

            if ($this->files->exists($destination) && ! $force) {
                $this->warn("Skipping (already exists): <comment>{$stub->getFilename()}</comment>");

                continue;
            }

            $this->files->copy($stub->getRealPath(), $destination);
            $this->info("✓ Published: <comment>{$stub->getFilename()}</comment>");
            $count++;
        }

        $this->line('');
        $this->info("Published {$count} stub(s) to <comment>{$targetPath}</comment>.");

        return self::SUCCESS;
    }
}
