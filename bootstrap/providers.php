<?php

return [
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\User\Infrastructure\Providers\UserServiceProvider::class,
    Modules\Authorization\Infrastructure\Providers\AuthorizationServiceProvider::class,
    Modules\Configuration\Infrastructure\Providers\ConfigurationServiceProvider::class,
];
