<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Services;
use Modules\Tenant\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\Tenant\Application\Contracts\BulkUploadTenantAttachmentsServiceInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantAttachmentRepositoryInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class BulkUploadTenantAttachmentsService implements BulkUploadTenantAttachmentsServiceInterface {
    public function __construct(
        private TenantRepositoryInterface $tenants,
        private TenantAttachmentRepositoryInterface $attachments,
        private AttachmentStorageStrategyInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        return [];
    }
}
