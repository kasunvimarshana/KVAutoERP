<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\Tenant\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\Tenant\Application\Contracts\BulkUploadTenantAttachmentsServiceInterface;
use Modules\Tenant\Application\Contracts\FindTenantAttachmentsServiceInterface;
use Modules\Tenant\Application\Services\BulkUploadTenantAttachmentsService;
use Modules\Tenant\Application\Services\FindTenantAttachmentsService;
use Modules\Tenant\Infrastructure\Storage\DefaultAttachmentStorageStrategy;

class TenantServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(AttachmentStorageStrategyInterface::class, DefaultAttachmentStorageStrategy::class);
        $this->app->bind(FindTenantAttachmentsServiceInterface::class, FindTenantAttachmentsService::class);
        $this->app->bind(BulkUploadTenantAttachmentsServiceInterface::class, BulkUploadTenantAttachmentsService::class);
    }
}
