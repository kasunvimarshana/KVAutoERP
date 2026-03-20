<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a CQRS Query DTO.
 */
class CqrsQueryGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'cqrs-query';
    }

    /**
     * Generate a CQRS Query DTO.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'].'Query';
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'application', 'Queries');
        $tokens    = $this->buildTokens($contextName, $options['name'], [
            'namespace' => $namespace,
            'className' => $className,
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'application', 'Queries', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
