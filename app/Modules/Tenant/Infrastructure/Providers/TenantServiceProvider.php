<?php

namespace Modules\Tenant\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantAttachmentRepositoryInterface;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantRepository;
use Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories\EloquentTenantAttachmentRepository;
use Modules\Tenant\Application\Services\CreateTenantService;
use Modules\Tenant\Application\Services\UpdateTenantService;
use Modules\Tenant\Application\Services\DeleteTenantService;
use Modules\Tenant\Application\Services\UpdateTenantConfigService;
use Modules\Tenant\Application\Services\UploadTenantAttachmentService;
use Modules\Tenant\Application\Services\DeleteTenantAttachmentService;

class TenantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(TenantRepositoryInterface::class, EloquentTenantRepository::class);
        $this->app->bind(TenantAttachmentRepositoryInterface::class, EloquentTenantAttachmentRepository::class);

        $this->app->bind(CreateTenantService::class, function ($app) {
            return new CreateTenantService($app->make(TenantRepositoryInterface::class));
        });
        $this->app->bind(UpdateTenantService::class, function ($app) {
            return new UpdateTenantService($app->make(TenantRepositoryInterface::class));
        });
        $this->app->bind(DeleteTenantService::class, function ($app) {
            return new DeleteTenantService($app->make(TenantRepositoryInterface::class));
        });
        $this->app->bind(UpdateTenantConfigService::class, function ($app) {
            return new UpdateTenantConfigService($app->make(TenantRepositoryInterface::class));
        });
        $this->app->bind(UploadTenantAttachmentService::class, function ($app) {
            return new UploadTenantAttachmentService(
                $app->make(TenantRepositoryInterface::class),
                $app->make(TenantAttachmentRepositoryInterface::class),
                $app->make(\Modules\Core\Application\Services\FileStorageServiceInterface::class)
            );
        });
        $this->app->bind(DeleteTenantAttachmentService::class, function ($app) {
            return new DeleteTenantAttachmentService(
                $app->make(TenantAttachmentRepositoryInterface::class),
                $app->make(\Modules\Core\Application\Services\FileStorageServiceInterface::class)
            );
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
    }
}
