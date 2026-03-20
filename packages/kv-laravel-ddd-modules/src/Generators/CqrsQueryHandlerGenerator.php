<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a CQRS Query Handler.
 */
class CqrsQueryHandlerGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'cqrs-query-handler';
    }

    /**
     * Generate a CQRS Query Handler.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $baseName    = $options['name'];
        $className   = $baseName.'QueryHandler';
        $force       = (bool) ($options['force'] ?? false);

        $namespace      = $this->buildNamespace($contextName, 'application', 'Handlers');
        $queryNamespace = $this->buildNamespace($contextName, 'application', 'Queries');

        $tokens = $this->buildTokens($contextName, $baseName, [
            'namespace'      => $namespace,
            'className'      => $className,
            'queryNamespace' => rtrim($queryNamespace, '\\'),
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'application', 'Handlers', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
