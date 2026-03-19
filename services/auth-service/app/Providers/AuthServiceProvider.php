<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\SessionRepositoryInterface;
use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Repositories\TokenRevocationRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuditServiceInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\SessionServiceInterface;
use App\Contracts\Services\TenantConfigServiceInterface;
use App\Contracts\Services\TokenServiceInterface;
use App\Repositories\AuditLogRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SessionRepository;
use App\Repositories\TenantRepository;
use App\Repositories\TokenRevocationRepository;
use App\Repositories\UserRepository;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\PermissionService;
use App\Services\SessionService;
use App\Services\TenantConfigService;
use App\Services\TokenService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * All interface → implementation bindings for the Auth service.
     * This is the single place to swap implementations at runtime.
     */
    private array $repositoryBindings = [
        UserRepositoryInterface::class            => UserRepository::class,
        TenantRepositoryInterface::class          => TenantRepository::class,
        SessionRepositoryInterface::class         => SessionRepository::class,
        TokenRevocationRepositoryInterface::class => TokenRevocationRepository::class,
        AuditLogRepositoryInterface::class        => AuditLogRepository::class,
        RoleRepositoryInterface::class            => RoleRepository::class,
        PermissionRepositoryInterface::class      => PermissionRepository::class,
    ];

    private array $serviceBindings = [
        AuthServiceInterface::class          => AuthService::class,
        TokenServiceInterface::class         => TokenService::class,
        SessionServiceInterface::class       => SessionService::class,
        AuditServiceInterface::class         => AuditService::class,
        PermissionServiceInterface::class    => PermissionService::class,
        TenantConfigServiceInterface::class  => TenantConfigService::class,
    ];

    public function register(): void
    {
        foreach ($this->repositoryBindings as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }

        foreach ($this->serviceBindings as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }

    public function boot(): void
    {
        // Bindings are resolved lazily — nothing to bootstrap here
    }
}
