<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AttachmentServiceContract;
use App\Contracts\PolicyServiceContract;
use App\Contracts\RoleServiceContract;
use App\Contracts\TenantServiceContract;
use App\Contracts\UserServiceContract;
use App\Services\AttachmentService;
use App\Services\PolicyService;
use App\Services\RoleService;
use App\Services\TenantService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserServiceContract::class, UserService::class);
        $this->app->singleton(TenantServiceContract::class, TenantService::class);
        $this->app->singleton(RoleServiceContract::class, RoleService::class);
        $this->app->singleton(AttachmentServiceContract::class, AttachmentService::class);
        $this->app->singleton(PolicyServiceContract::class, PolicyService::class);
    }

    public function boot(): void
    {
        //
    }
}
