<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a Domain Repository Interface.
 */
class RepositoryInterfaceGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'repository-interface';
    }

    /**
     * Generate a Repository Interface.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'].'RepositoryInterface';
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'domain', 'Repositories');
        $tokens    = $this->buildTokens($contextName, $options['name'], [
            'namespace' => $namespace,
            'className' => $className,
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'domain', 'Repositories', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
