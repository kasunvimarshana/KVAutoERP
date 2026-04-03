<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Services;
use Modules\Tenant\Application\Contracts\AttachmentStorageStrategyInterface;
use Modules\Tenant\Application\Contracts\DeleteTenantAttachmentServiceInterface;
use Modules\Tenant\Domain\Exceptions\AttachmentNotFoundException;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantAttachmentRepositoryInterface;

class DeleteTenantAttachmentService implements DeleteTenantAttachmentServiceInterface {
    public function __construct(
        private TenantAttachmentRepositoryInterface $attachments,
        private AttachmentStorageStrategyInterface $storage
    ) {}

    public function execute(array $data = []): mixed {
        return $this->handle($data);
    }

    protected function handle(array $data): bool {
        $attachment = $this->attachments->find((int)$data['attachment_id']);
        if (!$attachment) {
            throw new AttachmentNotFoundException($data['attachment_id']);
        }

        $this->storage->delete($attachment->getFilePath());
        return $this->attachments->delete($attachment->getId());
    }
}
