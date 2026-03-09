<?php

declare(strict_types=1);

return [

    'name'     => env('APP_NAME', 'Auth Service'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool) env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'version'  => env('APP_VERSION', '1.0.0'),

    'timezone' => 'UTC',
    'locale'   => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',

    'key'    => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    'maintenance' => ['driver' => 'file'],

    'providers' => [
        // Laravel Framework Service Providers
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        // Third-party
        Laravel\Passport\PassportServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        Spatie\Activitylog\ActivitylogServiceProvider::class,

        // Application
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\TenantServiceProvider::class,
    ],

    'aliases' => Illuminate\Support\Facades\Facade::defaultAliases()->toArray(),

];
