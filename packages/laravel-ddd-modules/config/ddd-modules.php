<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modules Base Path
    |--------------------------------------------------------------------------
    | The base path where DDD modules will be generated.
    */
    'modules_path' => app_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Modules Base Namespace
    |--------------------------------------------------------------------------
    */
    'modules_namespace' => 'App\\Modules',

    /*
    |--------------------------------------------------------------------------
    | Module Directory Structure
    |--------------------------------------------------------------------------
    | Define which folders are created per layer. You can add/remove folders.
    */
    'structure' => [
        'Domain' => [
            'Entities',
            'ValueObjects',
            'Aggregates',
            'Repositories',
            'Services',
            'Events',
            'Policies',
            'Enums',
            'Specifications',
            'Exceptions',
            'Traits',
            'Contracts',
        ],
        'Application' => [
            'DTOs',
            'UseCases',
            'Commands',
            'Queries',
            'Handlers',
            'Mappers',
            'Validators',
            'Services',
            'Contracts',
            'Exceptions',
        ],
        'Infrastructure' => [
            'Persistence/Eloquent',
            'Persistence/Repositories',
            'Persistence/Migrations',
            'Persistence/Factories',
            'Persistence/Seeders',
            'Persistence/Casts',
            'Services',
            'Events',
            'Jobs',
            'Notifications',
            'Providers',
            'Logging',
            'Integrations',
        ],
        'Presentation' => [
            'Http/Controllers/Api',
            'Http/Controllers/Web',
            'Http/Requests',
            'Http/Resources',
            'Http/Middleware',
            'Http/Routes',
            'Http/Exceptions',
            'Console/Commands',
            'Views',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stubs Configuration
    |--------------------------------------------------------------------------
    | Control which stub files get generated. Set to false to skip.
    | You can also override the stub path to use your own custom stubs.
    */
    'stubs' => [
        'path' => null, // null = use package default stubs
        'generate' => [
            'provider'              => true,
            'entity'                => true,
            'value_object'          => true,
            'aggregate'             => true,
            'repository_interface'  => true,
            'domain_service'        => true,
            'domain_event'          => true,
            'domain_exception'      => true,
            'domain_enum'           => true,
            'domain_policy'         => true,
            'specification'         => true,
            'use_case'              => true,
            'dto'                   => true,
            'mapper'                => true,
            'validator'             => true,
            'cqrs_command'          => true,
            'cqrs_query'            => true,
            'cqrs_handler'          => true,
            'application_exception' => true,
            'eloquent_model'        => true,
            'eloquent_repository'   => true,
            'migration'             => true,
            'factory'               => true,
            'seeder'                => true,
            'job'                   => true,
            'listener'              => true,
            'notification'          => true,
            'cast'                  => true,
            'api_controller'        => true,
            'web_controller'        => true,
            'form_request'          => true,
            'api_resource'          => true,
            'api_routes'            => true,
            'web_routes'            => true,
            'console_command'       => true,
            'middleware'            => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    | Automatically discover and register module service providers.
    */
    'auto_discover' => true,

    /*
    |--------------------------------------------------------------------------
    | Shared Module Name
    |--------------------------------------------------------------------------
    | Name for the cross-cutting shared module.
    */
    'shared_module' => 'Shared',
];
