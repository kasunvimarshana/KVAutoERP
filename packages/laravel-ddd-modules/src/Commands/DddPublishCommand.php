<?php

namespace LaravelDddModules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class DddPublishCommand extends Command
{
    protected $signature = 'ddd:publish
                            {--stubs : Publish stub templates}
                            {--config : Publish configuration file}
                            {--force : Overwrite existing published files}';

    protected $description = 'Publish DDD Modules package assets (stubs and/or config)';

    public function handle(): int
    {
        $publishStubs  = $this->option('stubs');
        $publishConfig = $this->option('config');
        $force         = $this->option('force');

        // If neither flag, publish both
        if (! $publishStubs && ! $publishConfig) {
            $publishStubs  = true;
            $publishConfig = true;
        }

        if ($publishConfig) {
            $this->publishConfig($force);
        }

        if ($publishStubs) {
            $this->publishStubs($force);
        }

        return self::SUCCESS;
    }

    protected function publishConfig(bool $force): void
    {
        $src  = __DIR__ . '/../../config/ddd-modules.php';
        $dest = config_path('ddd-modules.php');

        $files = new Filesystem();

        if ($files->exists($dest) && ! $force) {
            $this->warn('Config already published at [' . $dest . ']. Use --force to overwrite.');
            return;
        }

        $files->ensureDirectoryExists(dirname($dest));
        $files->copy($src, $dest);
        $this->info('<fg=green>Published</> config to <comment>' . $dest . '</comment>');
    }

    protected function publishStubs(bool $force): void
    {
        $src   = __DIR__ . '/../../stubs';
        $dest  = base_path('stubs/ddd-modules');
        $files = new Filesystem();

        $stubFiles = $files->allFiles($src);
        $published = 0;
        $skipped   = 0;

        foreach ($stubFiles as $file) {
            $relative   = $file->getRelativePathname();
            $targetPath = "{$dest}/{$relative}";

            if ($files->exists($targetPath) && ! $force) {
                $skipped++;
                continue;
            }

            $files->ensureDirectoryExists(dirname($targetPath));
            $files->copy($file->getRealPath(), $targetPath);
            $this->line("  <fg=green>+</> stubs/ddd-modules/{$relative}");
            $published++;
        }

        if ($published > 0) {
            $this->info("<fg=green>Published</> {$published} stub(s) to <comment>{$dest}</comment>");
        }

        if ($skipped > 0) {
            $this->warn("{$skipped} stub(s) already exist — skipped. Use --force to overwrite.");
        }
    }
}
