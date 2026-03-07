<?php

namespace App\Providers;

use App\Modules\Inventory\Events\InventoryUpdated;
use App\Modules\Inventory\Listeners\HandleInventoryUpdate;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Listeners\HandleOrderCreated;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Listeners\NotifyInventoryOnProductCreated;
use App\Modules\Product\Listeners\NotifyInventoryOnProductDeleted;
use App\Modules\User\Events\UserCreated;
use App\Modules\User\Events\UserDeleted;
use App\Modules\User\Events\UserUpdated;
use App\Modules\User\Listeners\SyncUserWithKeycloak;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserCreated::class => [
            SyncUserWithKeycloak::class . '@handleUserCreated',
        ],
        UserUpdated::class => [
            SyncUserWithKeycloak::class . '@handleUserUpdated',
        ],
        UserDeleted::class => [
            SyncUserWithKeycloak::class . '@handleUserDeleted',
        ],
        ProductCreated::class => [
            NotifyInventoryOnProductCreated::class,
        ],
        ProductDeleted::class => [
            NotifyInventoryOnProductDeleted::class,
        ],
        ProductUpdated::class => [],
        InventoryUpdated::class => [
            HandleInventoryUpdate::class,
        ],
        OrderCreated::class => [
            HandleOrderCreated::class,
        ],
        OrderCompleted::class => [],
        OrderCancelled::class => [],
    ];
}
