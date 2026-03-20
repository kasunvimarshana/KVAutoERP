<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Namespace Root
    |--------------------------------------------------------------------------
    | The root namespace for your application modules/contexts.
    */
    'namespace_root' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Base Path
    |--------------------------------------------------------------------------
    | The base directory (relative to the Laravel app root) where contexts live.
    */
    'base_path' => 'app',

    /*
    |--------------------------------------------------------------------------
    | Architecture Mode
    |--------------------------------------------------------------------------
    | The architecture mode. Currently supports 'ddd'.
    */
    'architecture_mode' => 'ddd',

    /*
    |--------------------------------------------------------------------------
    | Layers
    |--------------------------------------------------------------------------
    | Mapping of layer keys to directory names.
    */
    'layers' => [
        'domain'         => 'Domain',
        'application'    => 'Application',
        'infrastructure' => 'Infrastructure',
        'presentation'   => 'Presentation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain Directories
    |--------------------------------------------------------------------------
    | Sub-directories created inside the Domain layer.
    */
    'domain_directories' => [
        'Entities',
        'ValueObjects',
        'Repositories',
        'Events',
        'Services',
        'Specifications',
        'Exceptions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Directories
    |--------------------------------------------------------------------------
    | Sub-directories created inside the Application layer.
    */
    'application_directories' => [
        'Commands',
        'Queries',
        'Handlers',
        'DTOs',
        'Services',
    ],

    /*
    |--------------------------------------------------------------------------
    | Infrastructure Directories
    |--------------------------------------------------------------------------
    | Sub-directories created inside the Infrastructure layer.
    */
    'infrastructure_directories' => [
        'Persistence',
        'Repositories',
        'Services',
        'Http',
        'Jobs',
        'Notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | Presentation Directories
    |--------------------------------------------------------------------------
    | Sub-directories created inside the Presentation layer.
    */
    'presentation_directories' => [
        'Http/Controllers',
        'Http/Requests',
        'Http/Resources',
        'Routes',
        'Console',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shared Kernel Path
    |--------------------------------------------------------------------------
    | Directory name for the SharedKernel (relative to base_path).
    */
    'shared_kernel_path' => 'SharedKernel',

    /*
    |--------------------------------------------------------------------------
    | Auto Discover Contexts
    |--------------------------------------------------------------------------
    | When true, the package automatically discovers bounded contexts.
    */
    'auto_discover_contexts' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto Register Providers
    |--------------------------------------------------------------------------
    | When true, context service providers are automatically registered.
    */
    'auto_register_providers' => true,

    /*
    |--------------------------------------------------------------------------
    | Custom Stubs Path
    |--------------------------------------------------------------------------
    | Absolute path to a directory containing custom stub overrides.
    | Set to null to use the package's built-in stubs.
    */
    'stubs_path' => null,

    /*
    |--------------------------------------------------------------------------
    | File Permissions
    |--------------------------------------------------------------------------
    | Permissions applied when creating directories and files.
    */
    'file_permissions' => [
        'directories' => 0755,
        'files'       => 0644,
    ],
];
