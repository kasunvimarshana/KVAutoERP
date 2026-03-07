<?php
namespace App\Providers;

use App\Events\InventoryUpdated;
use App\Events\StockLow;
use App\Listeners\HandleInventoryUpdated;
use App\Listeners\HandleStockLow;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InventoryUpdated::class => [
            HandleInventoryUpdated::class,
        ],
        StockLow::class => [
            HandleStockLow::class,
        ],
    ];

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
