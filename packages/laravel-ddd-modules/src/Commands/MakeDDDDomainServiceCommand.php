<?php

namespace LaravelDddModules\Commands;

class MakeDDDDomainServiceCommand extends AbstractDddCommand
{
    protected $signature = 'make:ddd-domain-service
                            {module : The module name}
                            {name : The domain service name}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a Domain Service class within a DDD module';

    protected function stubName(): string
    {
        return 'DomainService.stub';
    }

    protected function targetPath(string $modulesPath, string $moduleName, string $artifactName): string
    {
        return "{$modulesPath}/{$moduleName}/Domain/Services/{$artifactName}DomainService.php";
    }

    protected function artifactLabel(): string
    {
        return 'Domain Service';
    }

    public function handle(): int
    {
        return $this->generateSingle(
            $this->argument('module'),
            $this->argument('name')
        );
    }
}
