<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a Data Transfer Object (DTO).
 */
class DtoGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'dto';
    }

    /**
     * Generate a DTO class.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'].'DTO';
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'application', 'DTOs');
        $tokens    = $this->buildTokens($contextName, $options['name'], [
            'namespace' => $namespace,
            'className' => $className,
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'application', 'DTOs', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
