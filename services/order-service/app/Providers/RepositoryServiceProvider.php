<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Order\Repositories\Interfaces\OrderRepositoryInterface;
use App\Infrastructure\Repositories\OrderRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider for Order Service.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        OrderRepositoryInterface::class => OrderRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}
