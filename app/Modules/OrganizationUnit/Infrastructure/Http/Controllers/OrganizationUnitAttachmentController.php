<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Http\Controllers;
use Modules\OrganizationUnit\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\OrganizationUnit\Application\Contracts\BulkUploadOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\FindOrganizationUnitAttachmentsServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\ReplaceOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Application\Contracts\UpdateOrganizationUnitAttachmentServiceInterface;
use Modules\OrganizationUnit\Infrastructure\Http\Requests\UpdateOrganizationUnitAttachmentRequest;
use Modules\OrganizationUnit\Infrastructure\Http\Requests\UploadOrganizationUnitAttachmentRequest;

class OrganizationUnitAttachmentController {
    public function __construct(
        private FindOrganizationUnitAttachmentsServiceInterface $finder,
        private AttachmentStorageStrategyInterface $storage,
        private BulkUploadOrganizationUnitAttachmentsServiceInterface $bulkUpload,
        private UpdateOrganizationUnitAttachmentServiceInterface $updater,
        private ReplaceOrganizationUnitAttachmentServiceInterface $replacer
    ) {}

    public function index($orgUnitId) {}
    public function store(UploadOrganizationUnitAttachmentRequest $request, $orgUnitId) {}
    public function storeMany(UploadOrganizationUnitAttachmentRequest $request, $orgUnitId) {}
    public function destroy($orgUnitId, $id) {}
    public function update(UpdateOrganizationUnitAttachmentRequest $request, $orgUnitId, $id) {}
    public function replace(UploadOrganizationUnitAttachmentRequest $request, $orgUnitId, $id) {}
}
