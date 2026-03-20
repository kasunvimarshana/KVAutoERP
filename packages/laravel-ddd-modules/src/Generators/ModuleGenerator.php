<?php

namespace LaravelDddModules\Generators;

use Illuminate\Support\Str;

class ModuleGenerator extends AbstractGenerator
{

    /**
     * Generate the full DDD module structure.
     */
    public function generate(string $moduleName, array $options = []): array
    {
        $moduleName = Str::studly($moduleName);
        $modulesPath = config('ddd-modules.modules_path', app_path('Modules'));
        $modulesNamespace = config('ddd-modules.modules_namespace', 'App\\Modules');
        $structure = config('ddd-modules.structure', []);
        $stubsConfig = config('ddd-modules.stubs.generate', []);

        $basePath = "{$modulesPath}/{$moduleName}";
        $baseNamespace = "{$modulesNamespace}\\{$moduleName}";

        $createdDirs = [];
        $createdFiles = [];

        // Create directory structure
        foreach ($structure as $layer => $subfolders) {
            foreach ($subfolders as $subfolder) {
                $fullPath = "{$basePath}/{$layer}/{$subfolder}";
                $this->files->makeDirectory($fullPath, 0755, true, true);
                $createdDirs[] = $fullPath;

                // Create .gitkeep to preserve empty directories
                $gitkeep = "{$fullPath}/.gitkeep";
                if (! $this->files->exists($gitkeep)) {
                    $this->files->put($gitkeep, '');
                }
            }
        }

        // Generate stub files
        $replacements = $this->buildReplacements($moduleName, $baseNamespace);
        $generatedFiles = $this->generateStubFiles($moduleName, $basePath, $baseNamespace, $replacements, $stubsConfig);
        $createdFiles = array_merge($createdFiles, $generatedFiles);

        return [
            'module'       => $moduleName,
            'path'         => $basePath,
            'namespace'    => $baseNamespace,
            'directories'  => $createdDirs,
            'files'        => $createdFiles,
        ];
    }

    /**
     * Build stub replacement variables.
     */
    public function buildReplacements(string $moduleName, string $baseNamespace): array
    {
        return [
            '{{ module }}'            => $moduleName,
            '{{ moduleLower }}'       => Str::lower($moduleName),
            '{{ moduleSnake }}'       => Str::snake($moduleName),
            '{{ moduleKebab }}'       => Str::kebab($moduleName),
            '{{ moduleCamel }}'       => Str::camel($moduleName),
            '{{ modulePlural }}'      => Str::plural($moduleName),
            '{{ modulePluralLower }}' => Str::lower(Str::plural($moduleName)),
            '{{ moduleSnakePlural }}' => Str::snake(Str::plural($moduleName)),
            '{{ namespace }}'         => $baseNamespace,
            '{{ domainNamespace }}'   => "{$baseNamespace}\\Domain",
            '{{ appNamespace }}'      => "{$baseNamespace}\\Application",
            '{{ infraNamespace }}'    => "{$baseNamespace}\\Infrastructure",
            '{{ presNamespace }}'     => "{$baseNamespace}\\Presentation",
            '{{ date }}'              => now()->format('Y_m_d'),
            '{{ datetime }}'          => now()->toDateTimeString(),
        ];
    }

    /**
     * Generate all stub files for the module.
     */
    protected function generateStubFiles(
        string $moduleName,
        string $basePath,
        string $baseNamespace,
        array $replacements,
        array $stubsConfig
    ): array {
        $generated = [];
        $stubPath = $this->resolveStubPath();

        $fileMap = $this->getStubFileMap($moduleName, $basePath, $baseNamespace);

        foreach ($fileMap as $stubKey => $fileInfo) {
            if (! ($stubsConfig[$stubKey] ?? true)) {
                continue;
            }

            $stubFile = "{$stubPath}/{$fileInfo['stub']}";
            if (! $this->files->exists($stubFile)) {
                continue;
            }

            $targetPath = $fileInfo['target'];
            $targetDir = dirname($targetPath);

            if (! $this->files->isDirectory($targetDir)) {
                $this->files->makeDirectory($targetDir, 0755, true, true);
            }

            if (! $this->files->exists($targetPath)) {
                $compiled = $this->stubCompiler->compile($stubFile, $replacements);
                $this->files->put($targetPath, $compiled);
                $generated[] = $targetPath;
            }
        }

        return $generated;
    }

    /**
     * Get the stub-to-target file mapping.
     */
    public function getStubFileMap(string $moduleName, string $basePath, string $baseNamespace): array
    {
        return [
            'provider' => [
                'stub'   => 'ServiceProvider.stub',
                'target' => "{$basePath}/Infrastructure/Providers/{$moduleName}ServiceProvider.php",
            ],
            'entity' => [
                'stub'   => 'Entity.stub',
                'target' => "{$basePath}/Domain/Entities/{$moduleName}Entity.php",
            ],
            'value_object' => [
                'stub'   => 'ValueObject.stub',
                'target' => "{$basePath}/Domain/ValueObjects/{$moduleName}Id.php",
            ],
            'aggregate' => [
                'stub'   => 'Aggregate.stub',
                'target' => "{$basePath}/Domain/Aggregates/{$moduleName}Aggregate.php",
            ],
            'repository_interface' => [
                'stub'   => 'RepositoryInterface.stub',
                'target' => "{$basePath}/Domain/Repositories/{$moduleName}RepositoryInterface.php",
            ],
            'domain_service' => [
                'stub'   => 'DomainService.stub',
                'target' => "{$basePath}/Domain/Services/{$moduleName}DomainService.php",
            ],
            'domain_event' => [
                'stub'   => 'DomainEvent.stub',
                'target' => "{$basePath}/Domain/Events/{$moduleName}Created.php",
            ],
            'use_case' => [
                'stub'   => 'UseCase.stub',
                'target' => "{$basePath}/Application/UseCases/Create{$moduleName}UseCase.php",
            ],
            'dto' => [
                'stub'   => 'Dto.stub',
                'target' => "{$basePath}/Application/DTOs/Create{$moduleName}DTO.php",
            ],
            'eloquent_model' => [
                'stub'   => 'EloquentModel.stub',
                'target' => "{$basePath}/Infrastructure/Persistence/Eloquent/{$moduleName}Model.php",
            ],
            'eloquent_repository' => [
                'stub'   => 'EloquentRepository.stub',
                'target' => "{$basePath}/Infrastructure/Persistence/Repositories/Eloquent{$moduleName}Repository.php",
            ],
            'api_controller' => [
                'stub'   => 'ApiController.stub',
                'target' => "{$basePath}/Presentation/Http/Controllers/Api/{$moduleName}Controller.php",
            ],
            'web_controller' => [
                'stub'   => 'WebController.stub',
                'target' => "{$basePath}/Presentation/Http/Controllers/Web/{$moduleName}Controller.php",
            ],
            'form_request' => [
                'stub'   => 'FormRequest.stub',
                'target' => "{$basePath}/Presentation/Http/Requests/Store{$moduleName}Request.php",
            ],
            'api_resource' => [
                'stub'   => 'ApiResource.stub',
                'target' => "{$basePath}/Presentation/Http/Resources/{$moduleName}Resource.php",
            ],
            'api_routes' => [
                'stub'   => 'routes-api.stub',
                'target' => "{$basePath}/Presentation/Http/Routes/api.php",
            ],
            'web_routes' => [
                'stub'   => 'routes-web.stub',
                'target' => "{$basePath}/Presentation/Http/Routes/web.php",
            ],
            'specification' => [
                'stub'   => 'Specification.stub',
                'target' => "{$basePath}/Domain/Specifications/{$moduleName}IsActiveSpecification.php",
            ],
            'domain_enum' => [
                'stub'   => 'DomainEnum.stub',
                'target' => "{$basePath}/Domain/Enums/{$moduleName}Status.php",
            ],
            'domain_policy' => [
                'stub'   => 'DomainPolicy.stub',
                'target' => "{$basePath}/Domain/Policies/{$moduleName}Policy.php",
            ],
            'domain_exception' => [
                'stub'   => 'DomainException.stub',
                'target' => "{$basePath}/Domain/Exceptions/{$moduleName}NotFoundException.php",
            ],
            'mapper' => [
                'stub'   => 'Mapper.stub',
                'target' => "{$basePath}/Application/Mappers/{$moduleName}Mapper.php",
            ],
            'validator' => [
                'stub'   => 'Validator.stub',
                'target' => "{$basePath}/Application/Validators/{$moduleName}Validator.php",
            ],
            'cqrs_command' => [
                'stub'   => 'CqrsCommand.stub',
                'target' => "{$basePath}/Application/Commands/{$moduleName}Command.php",
            ],
            'cqrs_query' => [
                'stub'   => 'CqrsQuery.stub',
                'target' => "{$basePath}/Application/Queries/{$moduleName}Query.php",
            ],
            'cqrs_handler' => [
                'stub'   => 'CqrsHandler.stub',
                'target' => "{$basePath}/Application/Handlers/{$moduleName}Handler.php",
            ],
            'application_exception' => [
                'stub'   => 'ApplicationException.stub',
                'target' => "{$basePath}/Application/Exceptions/{$moduleName}ApplicationException.php",
            ],
            'migration' => [
                'stub'   => 'Migration.stub',
                'target' => "{$basePath}/Infrastructure/Persistence/Migrations/" . date('Y_m_d_His') . "_create_{$this->getSnakePlural($moduleName)}_table.php",
            ],
            'factory' => [
                'stub'   => 'Factory.stub',
                'target' => "{$basePath}/Infrastructure/Persistence/Factories/{$moduleName}Factory.php",
            ],
            'seeder' => [
                'stub'   => 'Seeder.stub',
                'target' => "{$basePath}/Infrastructure/Persistence/Seeders/{$moduleName}Seeder.php",
            ],
            'job' => [
                'stub'   => 'Job.stub',
                'target' => "{$basePath}/Infrastructure/Jobs/Process{$moduleName}Job.php",
            ],
            'listener' => [
                'stub'   => 'Listener.stub',
                'target' => "{$basePath}/Infrastructure/Events/{$moduleName}CreatedListener.php",
            ],
            'notification' => [
                'stub'   => 'Notification.stub',
                'target' => "{$basePath}/Infrastructure/Notifications/{$moduleName}Notification.php",
            ],
            'cast' => [
                'stub'   => 'Cast.stub',
                'target' => "{$basePath}/Infrastructure/Persistence/Casts/{$moduleName}IdCast.php",
            ],
            'console_command' => [
                'stub'   => 'ConsoleCommand.stub',
                'target' => "{$basePath}/Presentation/Console/Commands/{$moduleName}ConsoleCommand.php",
            ],
            'middleware' => [
                'stub'   => 'Middleware.stub',
                'target' => "{$basePath}/Presentation/Http/Middleware/{$moduleName}Middleware.php",
            ],
        ];
    }

    private function getSnakePlural(string $moduleName): string
    {
        return Str::snake(Str::plural($moduleName));
    }
}
