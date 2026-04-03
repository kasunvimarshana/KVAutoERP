<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Controllers;
use Modules\Tenant\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\Tenant\Application\Contracts\BulkUploadTenantAttachmentsServiceInterface;
use Modules\Tenant\Application\Contracts\FindTenantAttachmentsServiceInterface;
use Modules\Tenant\Infrastructure\Http\Requests\UploadTenantAttachmentRequest;

class TenantAttachmentController {
    public function __construct(
        private FindTenantAttachmentsServiceInterface $finder,
        private AttachmentStorageStrategyInterface $storage,
        private BulkUploadTenantAttachmentsServiceInterface $bulkUpload
    ) {}

    public function index($tenantId) {}
    public function store(UploadTenantAttachmentRequest $request, $tenantId) {}
    public function storeMany(UploadTenantAttachmentRequest $request, $tenantId) {}
    public function destroy($tenantId, $id) {}
}
