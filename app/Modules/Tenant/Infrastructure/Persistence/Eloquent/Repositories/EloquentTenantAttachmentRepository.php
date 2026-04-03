<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Persistence\Eloquent\Repositories;
use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\TenantAttachment;
use Modules\Tenant\Domain\RepositoryInterfaces\TenantAttachmentRepositoryInterface;

class EloquentTenantAttachmentRepository implements TenantAttachmentRepositoryInterface {
    public function find(int $id): ?TenantAttachment { return null; }
    public function findByUuid(string $uuid): ?TenantAttachment { return null; }
    public function getByTenant(int $tenantId, ?string $type = null): Collection { return collect(); }
    public function save(TenantAttachment $attachment): TenantAttachment { return $attachment; }
    public function delete(int $id): bool { return false; }
}
