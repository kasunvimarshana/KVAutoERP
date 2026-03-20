<?php

namespace LaravelDddModules\Generators;

use Illuminate\Filesystem\Filesystem;

class StubCompiler
{
    public function __construct(protected Filesystem $files) {}

    /**
     * Compile a stub file with given replacements.
     */
    public function compile(string $stubPath, array $replacements): string
    {
        $content = $this->files->get($stubPath);
        return $this->replace($content, $replacements);
    }

    /**
     * Replace all placeholders in content.
     */
    public function replace(string $content, array $replacements): string
    {
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }
}
