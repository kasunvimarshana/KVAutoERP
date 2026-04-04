<?php

declare(strict_types=1);

namespace Modules\Configuration\Domain\Events;

use Modules\Core\Domain\Events\BaseEvent;

class OrgUnitDeleted extends BaseEvent
{
    public function __construct(
        public readonly int $orgUnitId,
        public readonly int $tenantId,
    ) {
        parent::__construct($tenantId, $orgUnitId);
    }

    public function broadcastWith(): array
    {
        return array_merge(parent::broadcastWith(), [
            'orgUnitId' => $this->orgUnitId,
        ]);
    }
}
