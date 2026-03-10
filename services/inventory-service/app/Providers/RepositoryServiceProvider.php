<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\InventoryRepository;
use App\Repositories\Contracts\InventoryTransactionRepositoryInterface;
use App\Repositories\InventoryTransactionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(InventoryTransactionRepositoryInterface::class, InventoryTransactionRepository::class);
    }
}
