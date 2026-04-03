<?php
declare(strict_types=1);
namespace Modules\Tenant\Application\Contracts;
use Illuminate\Support\Collection;
use Modules\Tenant\Domain\Entities\TenantAttachment;

interface FindTenantAttachmentsServiceInterface {
    public function findByTenant(int $tenantId, ?string $type = null): Collection;
    public function findByUuid(string $uuid): ?TenantAttachment;
    public function find(int $id): ?TenantAttachment;
}
