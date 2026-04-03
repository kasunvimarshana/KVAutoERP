<?php
declare(strict_types=1);
namespace Modules\Tenant\Domain\RepositoryInterfaces;
use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\TenantAttachment;

interface TenantAttachmentRepositoryInterface {
    public function find(int $id): ?TenantAttachment;
    public function findByUuid(string $uuid): ?TenantAttachment;
    public function getByTenant(int $tenantId, ?string $type = null): Collection;
    public function save(TenantAttachment $attachment): TenantAttachment;
    public function delete(int $id): bool;
}
