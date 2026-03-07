<?php

namespace App\Providers;

use App\Events\ProductCreated;
use App\Events\ProductDeleted;
use App\Events\ProductUpdated;
use App\Listeners\HandleProductCreated;
use App\Listeners\HandleProductDeleted;
use App\Listeners\HandleProductUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Cross-service event-listener mappings:
     *   - Service A dispatches ProductCreated/Updated/Deleted events
     *   - Service B listeners handle inventory management in response
     */
    public function boot(): void
    {
        // Service A -> Service B: Product created triggers inventory creation
        Event::listen(ProductCreated::class, HandleProductCreated::class);

        // Service A -> Service B: Product updated triggers inventory update
        Event::listen(ProductUpdated::class, HandleProductUpdated::class);

        // Service A -> Service B: Product deleted triggers inventory cleanup
        Event::listen(ProductDeleted::class, HandleProductDeleted::class);
    }
}
