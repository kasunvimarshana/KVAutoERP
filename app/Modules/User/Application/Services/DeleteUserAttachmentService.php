<?php

namespace Modules\User\Application\Services;

use Modules\Core\Application\Services\BaseService;
use Modules\User\Domain\RepositoryInterfaces\UserAttachmentRepositoryInterface;
use Modules\Core\Application\Services\FileStorageServiceInterface;
use Modules\Core\Domain\Contracts\Repositories\RepositoryInterface;

class DeleteUserAttachmentService extends BaseService
{
    public function __construct(
        protected UserAttachmentRepositoryInterface $attachmentRepo,
        protected FileStorageServiceInterface $storage
    ) {
        parent::__construct($attachmentRepo);
    }

    protected function handle(array $data): bool
    {
        $attachmentId = $data['attachment_id'];
        $attachment = $this->attachmentRepo->find($attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment not found');
        }

        $this->storage->delete($attachment->getFilePath());
        return $this->attachmentRepo->delete($attachmentId);
    }
}
