<?php

namespace LaravelDddModules\Commands;

class MakeDDDDomainEventCommand extends AbstractDddCommand
{
    protected $signature = 'make:ddd-domain-event
                            {module : The module name}
                            {name : The domain event name (e.g. OrderPlaced)}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a Domain Event class within a DDD module';

    protected function stubName(): string
    {
        return 'DomainEvent.stub';
    }

    protected function targetPath(string $modulesPath, string $moduleName, string $artifactName): string
    {
        return "{$modulesPath}/{$moduleName}/Domain/Events/{$artifactName}.php";
    }

    protected function artifactLabel(): string
    {
        return 'Domain Event';
    }

    public function handle(): int
    {
        return $this->generateSingle(
            $this->argument('module'),
            $this->argument('name')
        );
    }
}
