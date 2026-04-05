<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    Modules\Core\Infrastructure\Providers\CoreServiceProvider::class,
    Modules\Tenant\Infrastructure\Providers\TenantServiceProvider::class,
    Modules\Auth\Infrastructure\Providers\AuthServiceProvider::class,
    Modules\Configuration\Infrastructure\Providers\ConfigurationServiceProvider::class,
    Modules\Currency\Infrastructure\Providers\CurrencyServiceProvider::class,
    Modules\Tax\Infrastructure\Providers\TaxServiceProvider::class,
    Modules\Accounting\Infrastructure\Providers\AccountingServiceProvider::class,
];
