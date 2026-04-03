<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Services;
use Illuminate\Support\Collection;
use Modules\Tenant\Application\Contracts\FindTenantAttachmentsServiceInterface;
use Modules\Tenant\Domain\Entities\TenantAttachment;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantAttachmentRepositoryInterface;

class FindTenantAttachmentsService implements FindTenantAttachmentsServiceInterface {
    public function __construct(private TenantAttachmentRepositoryInterface $attachments) {}

    public function findByTenant(int $tenantId, ?string $type = null): Collection {
        return $this->attachments->getByTenant($tenantId, $type);
    }

    public function findByUuid(string $uuid): ?TenantAttachment {
        return $this->attachments->findByUuid($uuid);
    }

    public function find(int $id): ?TenantAttachment {
        return $this->attachments->find($id);
    }
}
