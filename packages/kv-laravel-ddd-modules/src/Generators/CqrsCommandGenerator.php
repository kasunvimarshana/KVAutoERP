<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a CQRS Command DTO.
 */
class CqrsCommandGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'cqrs-command';
    }

    /**
     * Generate a CQRS Command DTO.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $className   = $options['name'].'Command';
        $force       = (bool) ($options['force'] ?? false);

        $namespace = $this->buildNamespace($contextName, 'application', 'Commands');
        $tokens    = $this->buildTokens($contextName, $options['name'], [
            'namespace' => $namespace,
            'className' => $className,
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'application', 'Commands', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
