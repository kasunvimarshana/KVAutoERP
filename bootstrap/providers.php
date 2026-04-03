<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\User\Infrastructure\Providers\UserServiceProvider::class,
];
