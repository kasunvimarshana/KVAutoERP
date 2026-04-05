<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\Auth\Infrastructure\Providers\AuthServiceProvider::class,
    Modules\Configuration\Infrastructure\Providers\ConfigurationServiceProvider::class,
];
