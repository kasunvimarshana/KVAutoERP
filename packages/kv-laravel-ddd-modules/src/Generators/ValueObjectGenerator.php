<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a Domain Value Object class.
 */
class ValueObjectGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'value-object';
    }

    /**
     * Generate a Domain Value Object.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'];
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'domain', 'ValueObjects');
        $tokens    = $this->buildTokens($contextName, $className, ['namespace' => $namespace]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'domain', 'ValueObjects', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
