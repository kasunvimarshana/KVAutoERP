<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

use Illuminate\Filesystem\Filesystem;

/**
 * Generates the full directory structure for a new DDD bounded context.
 */
class ContextGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    public function getStubName(): string
    {
        return 'context-provider';
    }

    /**
     * Generate all directories and the ContextServiceProvider for a new bounded context.
     *
     * @param  array{context: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $force       = (bool) ($options['force'] ?? false);

        $basePath = (string) $this->config->get('ddd.base_path', 'app');
        $layers   = (array) $this->config->get('ddd.layers', [
            'domain'         => 'Domain',
            'application'    => 'Application',
            'infrastructure' => 'Infrastructure',
            'presentation'   => 'Presentation',
        ]);

        $contextRoot = $basePath.DIRECTORY_SEPARATOR.$contextName;

        // Create layer directories and their sub-directories
        $this->createLayerDirectories($contextRoot, $layers);

        // Generate the ContextServiceProvider
        $providerName = $contextName.'ServiceProvider';
        $namespace    = $this->buildNamespace($contextName, '', '');
        $namespace    = rtrim($namespace, '\\');

        $tokens = $this->buildTokens($contextName, $providerName, [
            'namespace' => $namespace,
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $contextRoot.DIRECTORY_SEPARATOR.$providerName.'.php';

        return $this->fileGenerator->generate($filePath, $content, $force);
    }

    /**
     * Create all layer directories and their configured sub-directories.
     *
     * @param  string  $contextRoot  Absolute path to the context root.
     * @param  array<string, string>  $layers
     * @return void
     */
    protected function createLayerDirectories(string $contextRoot, array $layers): void
    {
        $layerDirMap = [
            'domain'         => (array) $this->config->get('ddd.domain_directories', []),
            'application'    => (array) $this->config->get('ddd.application_directories', []),
            'infrastructure' => (array) $this->config->get('ddd.infrastructure_directories', []),
            'presentation'   => (array) $this->config->get('ddd.presentation_directories', []),
        ];

        foreach ($layers as $layerKey => $layerName) {
            $layerPath = $contextRoot.DIRECTORY_SEPARATOR.$layerName;
            $this->fileGenerator->ensureDirectoryExists($layerPath);

            $subDirs = $layerDirMap[$layerKey] ?? [];
            foreach ($subDirs as $subDir) {
                $this->fileGenerator->ensureDirectoryExists($layerPath.DIRECTORY_SEPARATOR.$subDir);
            }
        }
    }
}
