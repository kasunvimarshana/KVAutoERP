<?php

declare(strict_types=1);

namespace LaravelDDD\Generators;

/**
 * Generates a CQRS Command Handler.
 */
class CqrsCommandHandlerGenerator extends AbstractGenerator
{
    /** {@inheritdoc} */
    public function getStubName(): string
    {
        return 'cqrs-command-handler';
    }

    /**
     * Generate a CQRS Command Handler.
     *
     * @param  array{context: string, name: string, force?: bool}  $options
     * @return bool
     */
    public function generate(array $options): bool
    {
        $contextName = $options['context'];
        $baseName    = $options['name'];
        $className   = $baseName.'CommandHandler';
        $force       = (bool) ($options['force'] ?? false);

        $namespace        = $this->buildNamespace($contextName, 'application', 'Handlers');
        $commandNamespace = $this->buildNamespace($contextName, 'application', 'Commands');

        $tokens = $this->buildTokens($contextName, $baseName, [
            'namespace'        => $namespace,
            'className'        => $className,
            'commandNamespace' => rtrim($commandNamespace, '\\'),
        ]);

        $stubPath = $this->stubRenderer->getStubPath($this->getStubName());
        $content  = $this->stubRenderer->renderFile($stubPath, $tokens);
        $filePath = $this->buildFilePath($contextName, 'application', 'Handlers', $className);

        return $this->fileGenerator->generate($filePath, $content, $force);
    }
}
