<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Services;
use Illuminate\Support\Str;
use Modules\Tenant\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\Tenant\Application\Contracts\UploadTenantAttachmentServiceInterface;
use Modules\Tenant\Domain\Entities\TenantAttachment;
use Modules\Tenant\Domain\Exceptions\TenantNotFoundException;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantAttachmentRepositoryInterface;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantRepositoryInterface;

class UploadTenantAttachmentService implements UploadTenantAttachmentServiceInterface {
    public function __construct(
        private TenantRepositoryInterface $tenants,
        private TenantAttachmentRepositoryInterface $attachments,
        private AttachmentStorageStrategyInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): TenantAttachment {
        $tenant = $this->tenants->find((int)$data['tenant_id']);
        if (!$tenant) {
            throw new TenantNotFoundException($data['tenant_id']);
        }

        $file = $data['file'];
        $filePath = $this->storage->store($file, $tenant->getId());

        $attachment = new TenantAttachment(
            tenantId: $tenant->getId(),
            uuid: (string)Str::uuid(),
            name: $file->getClientOriginalName(),
            filePath: $filePath,
            mimeType: $file->getMimeType(),
            size: $file->getSize(),
            type: $data['type'] ?? null,
        );

        $saved = $this->attachments->save($attachment);

        if (($data['type'] ?? null) === 'logo') {
            $this->tenants->save($tenant);
        }

        return $saved;
    }
}
