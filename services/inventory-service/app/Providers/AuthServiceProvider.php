<?php
namespace App\Providers;

use App\Models\Inventory;
use App\Policies\InventoryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Inventory::class => InventoryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
