<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class OrgUnitUpdated extends BaseEvent
{
    public function __construct(
        int $tenantId,
        public readonly int $orgUnitId,
        ?int $orgUnitParentId = null,
    ) {
        parent::__construct($tenantId, $orgUnitParentId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), ['orgUnitId' => $this->orgUnitId]);
    }
}
