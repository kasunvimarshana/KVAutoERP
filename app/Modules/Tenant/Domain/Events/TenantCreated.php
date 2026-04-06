<?php
declare(strict_types=1);
namespace Modules\Tenant\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;
use Modules\Tenant\Domain\Entities\Tenant;

class TenantCreated extends BaseEvent
{
    public function __construct(
        public readonly Tenant $tenant,
        int $tenantId,
        ?int $orgUnitId = null,
    ) {
        parent::__construct($tenantId, $orgUnitId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'tenant_id' => $this->tenant->getId(),
            'name'      => $this->tenant->getName(),
            'slug'      => $this->tenant->getSlug(),
        ]);
    }
}
