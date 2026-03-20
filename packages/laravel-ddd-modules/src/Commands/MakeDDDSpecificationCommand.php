<?php

namespace LaravelDddModules\Commands;

class MakeDDDSpecificationCommand extends AbstractDddCommand
{
    protected $signature = 'make:ddd-specification
                            {module : The module name}
                            {name : The specification name (e.g. OrderIsActive)}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a Domain Specification class within a DDD module';

    protected function stubName(): string
    {
        return 'Specification.stub';
    }

    protected function targetPath(string $modulesPath, string $moduleName, string $artifactName): string
    {
        return "{$modulesPath}/{$moduleName}/Domain/Specifications/{$artifactName}Specification.php";
    }

    protected function artifactLabel(): string
    {
        return 'Specification';
    }

    public function handle(): int
    {
        return $this->generateSingle(
            $this->argument('module'),
            $this->argument('name')
        );
    }
}
