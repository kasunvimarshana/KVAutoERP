<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class OrgUnitMoved extends BaseEvent
{
    public function __construct(
        public readonly int $orgUnitId,
        public readonly int $tenantId,
        public readonly ?int $oldParentId,
        public readonly ?int $newParentId,
    ) {
        parent::__construct($tenantId, $orgUnitId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'orgUnitId'   => $this->orgUnitId,
            'oldParentId' => $this->oldParentId,
            'newParentId' => $this->newParentId,
        ]);
    }
}
