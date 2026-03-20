<?php

namespace LaravelDddModules\Commands;

class MakeDDDDtoCommand extends AbstractDddCommand
{
    protected $signature = 'make:ddd-dto
                            {module : The module name}
                            {name : The DTO name (e.g. CreateOrder)}
                            {--force : Overwrite if exists}';

    protected $description = 'Generate a Data Transfer Object (DTO) within a DDD module';

    protected function stubName(): string
    {
        return 'Dto.stub';
    }

    protected function targetPath(string $modulesPath, string $moduleName, string $artifactName): string
    {
        return "{$modulesPath}/{$moduleName}/Application/DTOs/{$artifactName}DTO.php";
    }

    protected function artifactLabel(): string
    {
        return 'DTO';
    }

    public function handle(): int
    {
        return $this->generateSingle(
            $this->argument('module'),
            $this->argument('name')
        );
    }
}
