<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\BulkUploadOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\ReplaceOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\Services\BulkUploadOrganizationUnitAttachmentsService;
use Modules\OrganizationUnit\Application\Services\FindOrganizationUnitAttachmentsService;
use Modules\OrganizationUnit\Application\Services\FindOrganizationUnitService;
use Modules\OrganizationUnit\Application\Services\ReplaceOrganizationUnitAttachmentService;
use Modules\OrganizationUnit\Application\Services\UpdateOrganizationUnitAttachmentService;
use Modules\OrganizationUnit\Infrastructure\Storage\DefaultAttachmentStorageStrategy;

class OrganizationUnitServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(AttachmentStorageStrategyInterface::class, DefaultAttachmentStorageStrategy::class);
        $this->app->bind(FindOrganizationUnitAttachmentsServiceInterface::class, FindOrganizationUnitAttachmentsService::class);
        $this->app->bind(BulkUploadOrganizationUnitAttachmentsServiceInterface::class, BulkUploadOrganizationUnitAttachmentsService::class);
        $this->app->bind(FindOrganizationUnitServiceInterface::class, FindOrganizationUnitService::class);
        $this->app->bind(UpdateOrganizationUnitAttachmentServiceInterface::class, UpdateOrganizationUnitAttachmentService::class);
        $this->app->bind(ReplaceOrganizationUnitAttachmentServiceInterface::class, ReplaceOrganizationUnitAttachmentService::class);
    }
}
