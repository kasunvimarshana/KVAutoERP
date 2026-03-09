<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Tenant\Repositories\Interfaces\TenantRepositoryInterface;
use App\Domain\User\Repositories\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Repositories\TenantRepository;
use App\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider.
 *
 * Binds repository interfaces to their concrete implementations.
 * Swap implementations here without modifying any consumer code.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $bindings = [
        TenantRepositoryInterface::class => TenantRepository::class,
        UserRepositoryInterface::class   => UserRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}
