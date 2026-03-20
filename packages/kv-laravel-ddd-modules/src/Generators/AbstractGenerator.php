<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

use Illuminate\Contracts\Config\Repository as Config;
use LaravelDDD\Support\FileGenerator;
use LaravelDDD\Support\StubRenderer;

/**
 * Base class for all DDD code generators.
 */
abstract class AbstractGenerator
{
    /**
     * @param  Config        $config        Laravel config repository.
     * @param  StubRenderer  $stubRenderer  Stub rendering service.
     * @param  FileGenerator $fileGenerator File writing service.
     */
    public function __construct(
        protected Config $config,
        protected StubRenderer $stubRenderer,
        protected FileGenerator $fileGenerator,
    ) {}

    /**
     * Run the generator with the given options.
     *
     * @param  array<string, mixed>  $options
     * @return bool  True on success.
     */
    abstract public function generate(array $options): bool;

    /**
     * Return the stub file name (without .stub extension) used by this generator.
     *
     * @return string
     */
    abstract public function getStubName(): string;

    /**
     * Build the fully-qualified PHP namespace for a class.
     *
     * Result: {namespace_root}\{context}\{layer}\{subPath}
     *
     * @param  string  $context   Context name (e.g. "ProductCatalog").
     * @param  string  $layer     Layer key as defined in config layers (e.g. "domain").
     * @param  string  $subPath   Optional additional sub-namespace path (e.g. "Entities").
     * @return string
     */
    protected function buildNamespace(string $context, string $layer, string $subPath = ''): string
    {
        $root   = rtrim((string) $this->config->get('ddd.namespace_root', 'App'), '\\');
        $layers = (array) $this->config->get('ddd.layers', []);
        $layerName = $layers[$layer] ?? ucfirst($layer);

        $parts = array_filter([$root, $context, $layerName, $subPath], fn ($p) => $p !== '');

        return implode('\\', $parts);
    }

    /**
     * Build the absolute filesystem path for a generated class file.
     *
     * Result: {base_path}/{context}/{layer}/{subPath}/{className}.php
     *
     * @param  string  $context    Context name.
     * @param  string  $layer      Layer key (e.g. "domain").
     * @param  string  $subPath    Sub-directory path within the layer (e.g. "Entities").
     * @param  string  $className  Class name (without .php extension).
     * @return string  Absolute path.
     */
    protected function buildFilePath(string $context, string $layer, string $subPath, string $className): string
    {
        $basePath  = rtrim((string) $this->config->get('ddd.base_path', 'app'), '/\\');
        $layers    = (array) $this->config->get('ddd.layers', []);
        $layerName = $layers[$layer] ?? ucfirst($layer);

        $parts = array_filter([$basePath, $context, $layerName, $subPath], fn ($p) => $p !== '');

        return implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR.$className.'.php';
    }

    /**
     * Build the standard token map used by stub renderers.
     *
     * @param  string  $contextName  Context name (e.g. "ProductCatalog").
     * @param  string  $className    Class name (e.g. "Product").
     * @param  array<string, string>  $extra  Additional tokens to merge.
     * @return array<string, string>
     */
    protected function buildTokens(string $contextName, string $className, array $extra = []): array
    {
        $contextNamespace        = $this->buildNamespace($contextName, '', '');
        $domainNamespace         = $this->buildNamespace($contextName, 'domain', '');
        $applicationNamespace    = $this->buildNamespace($contextName, 'application', '');
        $infrastructureNamespace = $this->buildNamespace($contextName, 'infrastructure', '');
        $presentationNamespace   = $this->buildNamespace($contextName, 'presentation', '');

        return array_merge([
            'className'               => $className,
            'contextName'             => $contextName,
            'contextKebab'            => $this->toKebabCase($contextName),
            'classSnake'              => $this->toSnakeCase($className),
            'classCamel'              => lcfirst($className),
            'contextNamespace'        => rtrim($contextNamespace, '\\'),
            'domainNamespace'         => rtrim($domainNamespace, '\\'),
            'applicationNamespace'    => rtrim($applicationNamespace, '\\'),
            'infrastructureNamespace' => rtrim($infrastructureNamespace, '\\'),
            'presentationNamespace'   => rtrim($presentationNamespace, '\\'),
            'year'                    => (string) date('Y'),
            'date'                    => date('Y-m-d'),
        ], $extra);
    }

    /**
     * Convert a PascalCase string to kebab-case.
     *
     * @param  string  $value
     * @return string
     */
    protected function toKebabCase(string $value): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '-$0', lcfirst($value)));
    }

    /**
     * Convert a PascalCase string to snake_case.
     *
     * @param  string  $value
     * @return string
     */
    protected function toSnakeCase(string $value): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '_$0', lcfirst($value)));
    }
}
