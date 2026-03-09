<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MessageBrokerServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Tenant\Providers\TenantServiceProvider::class,
    App\Modules\Inventory\Providers\InventoryServiceProvider::class,
    App\Modules\Order\Providers\OrderServiceProvider::class,
    App\Modules\Webhook\Providers\WebhookServiceProvider::class,
    App\Modules\Health\Providers\HealthServiceProvider::class,
];
