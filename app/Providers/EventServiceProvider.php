<?php

namespace App\Providers;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Listeners\ProductEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    public function boot(): void
    {
        Event::listen(ProductCreated::class, [ProductEventListener::class, 'handleCreated']);
        Event::listen(ProductUpdated::class, [ProductEventListener::class, 'handleUpdated']);
        Event::listen(ProductDeleted::class, [ProductEventListener::class, 'handleDeleted']);
    }
}
