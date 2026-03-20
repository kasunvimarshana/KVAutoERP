<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a Domain Aggregate Root class.
 */
class AggregateRootGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'aggregate-root';
    }

    /**
     * Generate an Aggregate Root.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'];
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'domain', '');
        $namespace = rtrim($namespace, '\\');
        $tokens    = $this->buildTokens($contextName, $className, ['namespace' => $namespace]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'domain', '', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
