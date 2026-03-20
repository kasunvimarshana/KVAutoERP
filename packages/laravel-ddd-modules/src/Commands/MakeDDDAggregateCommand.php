<?php

namespace LaravelDddModules\Commands;

class MakeDDDAggregateCommand extends AbstractDddCommand
{
    protected $signature = 'make:ddd-aggregate
                            {module : The module name}
                            {name : The aggregate root name}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a Domain Aggregate Root class within a DDD module';

    protected function stubName(): string
    {
        return 'Aggregate.stub';
    }

    protected function targetPath(string $modulesPath, string $moduleName, string $artifactName): string
    {
        return "{$modulesPath}/{$moduleName}/Domain/Aggregates/{$artifactName}Aggregate.php";
    }

    protected function artifactLabel(): string
    {
        return 'Aggregate';
    }

    public function handle(): int
    {
        return $this->generateSingle(
            $this->argument('module'),
            $this->argument('name')
        );
    }
}
