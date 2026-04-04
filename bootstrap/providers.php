<?php

declare(strict_types=1);

return [
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\User\Infrastructure\Providers\UserServiceProvider::class,
    Modules\Auth\Infrastructure\Providers\AuthorizationServiceProvider::class,
    Modules\Configuration\Infrastructure\Providers\ConfigurationServiceProvider::class,
    Modules\Warehouse\Infrastructure\Providers\WarehouseServiceProvider::class,
    Modules\Product\Infrastructure\Providers\ProductServiceProvider::class,
];
