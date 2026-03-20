<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a Domain Entity class.
 */
class EntityGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'entity';
    }

    /**
     * Generate a Domain Entity.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'];
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'domain', 'Entities');
        $tokens    = $this->buildTokens($contextName, $className, ['namespace' => $namespace]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'domain', 'Entities', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
