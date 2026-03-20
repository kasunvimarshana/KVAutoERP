<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates an Eloquent Repository implementation.
 */
class EloquentRepositoryGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'eloquent-repository';
    }

    /**
     * Generate an Eloquent Repository implementation.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $baseName    = $options['name'];
        $className   = 'Eloquent'.$baseName.'Repository';
        $force       = (bool) ($options['force'] ?? false);

        $namespace       = $this->buildNamespace($contextName, 'infrastructure', 'Persistence');
        $domainNamespace = $this->buildNamespace($contextName, 'domain', 'Repositories');

        $tokens = $this->buildTokens($contextName, $baseName, [
            'namespace'       => $namespace,
            'className'       => $className,
            'interfaceClass'  => $baseName.'RepositoryInterface',
            'domainNamespace' => rtrim($domainNamespace, '\\'),
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'infrastructure', 'Persistence', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
