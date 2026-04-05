<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\Auth\Infrastructure\Providers\AuthServiceProvider::class,
    Modules\UserProfile\Infrastructure\Providers\UserProfileServiceProvider::class,
    Modules\Configuration\Infrastructure\Providers\ConfigurationServiceProvider::class,
    Modules\OrgUnit\Infrastructure\Providers\OrgUnitServiceProvider::class,
    Modules\Accounting\Infrastructure\Providers\AccountingServiceProvider::class,
    Modules\Product\Infrastructure\Providers\ProductServiceProvider::class,
    Modules\Inventory\Infrastructure\Providers\InventoryServiceProvider::class,
];
