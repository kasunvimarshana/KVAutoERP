<?php

declare(strict_types=1);

namespace LaravelDDD\Support;

use InvalidArgumentException;

/**
 * Renders stub templates by replacing {{ token }} placeholders.
 */
class StubRenderer
{
    /**
     * @param  string  $defaultStubsPath  Absolute path to the package's built-in stubs directory.
     * @param  string|null  $customStubsPath  Optional path to user-supplied stubs (overrides defaults).
     */
    public function __construct(
        protected string $defaultStubsPath,
        protected ?string $customStubsPath = null,
    ) {}

    /**
     * Replace all {{ token }} placeholders in the given stub content.
     *
     * @param  string  $stub   Raw stub content.
     * @param  array<string, string>  $tokens  Map of token name => replacement value.
     * @return string  Rendered content.
     */
    public function render(string $stub, array $tokens): string
    {
        foreach ($tokens as $token => $value) {
            $stub = preg_replace('/\{\{\s*'.preg_quote($token, '/').'\s*\}\}/', (string) $value, $stub) ?? $stub;
        }

        return $stub;
    }

    /**
     * Read a stub file and render it with the given tokens.
     *
     * @param  string  $stubPath  Absolute path to the stub file.
     * @param  array<string, string>  $tokens
     * @return string
     *
     * @throws InvalidArgumentException When the stub file does not exist.
     */
    public function renderFile(string $stubPath, array $tokens): string
    {
        if (! file_exists($stubPath)) {
            throw new InvalidArgumentException("Stub file not found: {$stubPath}");
        }

        $content = file_get_contents($stubPath);

        if ($content === false) {
            throw new InvalidArgumentException("Could not read stub file: {$stubPath}");
        }

        return $this->render($content, $tokens);
    }

    /**
     * Resolve the absolute path to a named stub, preferring custom stubs when available.
     *
     * @param  string  $stubName  e.g. "entity" (without .stub extension) or "entity.stub".
     * @return string  Absolute path to the stub file.
     *
     * @throws InvalidArgumentException When no stub can be found.
     */
    public function getStubPath(string $stubName): string
    {
        // Ensure the name ends with .stub
        if (! str_ends_with($stubName, '.stub')) {
            $stubName .= '.stub';
        }

        // Check custom stubs directory first
        if ($this->customStubsPath !== null) {
            $customPath = rtrim($this->customStubsPath, '/\\').DIRECTORY_SEPARATOR.$stubName;
            if (file_exists($customPath)) {
                return $customPath;
            }
        }

        // Fall back to package built-in stubs
        $defaultPath = rtrim($this->defaultStubsPath, '/\\').DIRECTORY_SEPARATOR.$stubName;
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }

        throw new InvalidArgumentException("Stub '{$stubName}' not found in any stubs directory.");
    }
}
