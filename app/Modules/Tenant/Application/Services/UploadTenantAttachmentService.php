<?php

namespace Modules\Tenant\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantAttachmentRepositoryInterface;
use Modules\Tenant\Domain\Entities\TenantAttachment;
use Modules\Core\Application\Services\FileStorageServiceInterface;
use Illuminate\Support\Str;

class UploadTenantAttachmentService extends BaseService
{
    public function __construct(
        TenantRepositoryInterface $repository,
        protected TenantAttachmentRepositoryInterface $attachmentRepo,
        protected FileStorageServiceInterface $storage
    ) {
        parent::__construct($repository);
    }

    protected function handle(array $data): TenantAttachment
    {
        $tenantId = $data['tenant_id'];
        $fileInfo = $data['file'];
        $type = $data['type'] ?? null;
        $metadata = $data['metadata'] ?? [];

        $tenant = $this->repository->find($tenantId);
        if (!$tenant) {
            throw new \RuntimeException('Tenant not found');
        }

        $uuid = (string) Str::uuid();
        $path = $this->storage->store($fileInfo['tmp_path'], "tenants/{$tenantId}", $fileInfo['name']);

        $attachment = new TenantAttachment(
            tenantId: $tenantId,
            uuid: $uuid,
            name: $fileInfo['name'],
            filePath: $path,
            mimeType: $fileInfo['mime_type'],
            size: $fileInfo['size'],
            type: $type,
            metadata: $metadata
        );

        $saved = $this->attachmentRepo->save($attachment);

        // If this is a logo, update tenant's logo_path
        if ($type === 'logo') {
            $tenant->setLogoPath($path);
            $this->repository->save($tenant);
        }

        // Optionally add event
        return $saved;
    }
}
