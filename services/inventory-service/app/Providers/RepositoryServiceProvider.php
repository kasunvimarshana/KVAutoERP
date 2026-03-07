<?php

namespace App\Providers;

use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Infrastructure\Repositories\EloquentInventoryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        InventoryRepositoryInterface::class => EloquentInventoryRepository::class,
    ];

    public function register(): void
    {
        // Additional repositories can be registered here as the project grows.
    }
}
