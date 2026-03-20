<?php

declare(strict_types=1);

namespace LaravelDDD\Support;

use Illuminate\Filesystem\Filesystem;

/**
 * Generates PHP source files on the filesystem.
 */
class FileGenerator
{
    /**
     * @param  Filesystem  $files  Laravel filesystem abstraction.
     */
    public function __construct(
        protected Filesystem $files,
    ) {}

    /**
     * Write content to a file.
     *
     * @param  string  $filePath  Absolute path where the file should be written.
     * @param  string  $content   Content to write.
     * @param  bool    $force     Overwrite an existing file when true.
     * @return bool  True on success, false when the file already exists and $force is false.
     */
    public function generate(string $filePath, string $content, bool $force = false): bool
    {
        if ($this->files->exists($filePath) && ! $force) {
            return false;
        }

        $this->ensureDirectoryExists(dirname($filePath));

        $this->files->put($filePath, $content);

        return true;
    }

    /**
     * Ensure that a directory (and all parent directories) exists.
     *
     * @param  string  $path  Absolute directory path.
     * @return void
     */
    public function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }
    }

    /**
     * Determine whether a file already exists.
     *
     * @param  string  $path  Absolute file path.
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return $this->files->exists($path);
    }
}
