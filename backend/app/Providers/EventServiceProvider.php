<?php

namespace App\Providers;

use App\Modules\Inventory\Events\InventoryUpdated;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Listeners\HandleLowStockAlert;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Listeners\ReleaseInventoryOnOrderCancelled;
use App\Modules\Order\Listeners\ReserveInventoryOnOrderCreated;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Listeners\CreateInventoryOnProductCreated;
use App\Modules\Product\Listeners\DeleteInventoryOnProductDeleted;
use App\Modules\User\Events\UserCreated;
use App\Modules\User\Listeners\SendUserCreatedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserCreated::class => [
            SendUserCreatedNotification::class,
        ],
        ProductCreated::class => [
            CreateInventoryOnProductCreated::class,
        ],
        ProductDeleted::class => [
            DeleteInventoryOnProductDeleted::class,
        ],
        InventoryUpdated::class => [],
        LowStockAlert::class => [
            HandleLowStockAlert::class,
        ],
        OrderCreated::class => [
            ReserveInventoryOnOrderCreated::class,
        ],
        OrderCancelled::class => [
            ReleaseInventoryOnOrderCancelled::class,
        ],
    ];
}
